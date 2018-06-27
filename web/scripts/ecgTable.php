<?php
require_once 'table_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

class ecg_generator extends table_generator
{
  public function set_statistic($_stat)
  {
    $this->statistic = $_stat;
  }

  public function set_standard_deviation_scale($_stdev)
  {
    $this->standard_deviation_scale = $_stdev;
  }

  protected function build_data()
  {
    global $db;

    $filesize_min=0;
    $filesize_max=0;
    if('mode' == $this->statistic)
    {
      $minsz=0;
      $maxsz=0;
      $mode=0;
      $sql = sprintf(
        'select fsz, count(fsz) as freq '.
        'from ( '.
        '  select round( '.
        '    substring_index( '.
        '      substring_index(qcdata, ",", 1 ), ":", -1)/1024.0,0) as fsz '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and qcdata is not null '.
        '  and s.name="ecg" '.
        ') as t '.
        'where fsz>0 '.
        'group by fsz '.
        'order by freq desc limit 1', $this->rank);

      $res = $db->get_row( $sql );
      $mode = $res['fsz'];

      $sql = sprintf(
        'select min(fsz) as minsz, max(fsz) as maxsz '.
        'from ( '.
        '  select round('.
        '    substring_index( '.
        '      substring_index(qcdata, ",", 1), ":", -1)/1024.0) as fsz '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and qcdata is not null '.
        '  and s.name="ecg" '.
        ') as t '.
        'where fsz>0', $this->rank);

      $res = $db->get_row( $sql );
      $minsz = $res['minsz'];
      $maxsz = $res['maxsz'];
      $filesize_min = max(intval(($minsz + 0.5*($mode-$minsz))*1024),0);
      $filesize_max = intval(($mode + 0.5*($maxsz-$mode))*1024);
    }
    else
    {
      $avg=0;
      $stdev=0;
      $sql = sprintf(
        'select avg(fsz) as favg '.
        'from ( '.
        '  select round( '.
        '    substring_index( '.
        '      substring_index(qcdata, ",", 1 ), ":", -1)/1024.0,0) as fsz '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and qcdata is not null '.
        '  and s.name="ecg" '.
        ') as t '.
        'where fsz>0', $this->rank);

      $res = $db->get_row( $sql );
      $avg = $res['favg'];

      $sql = sprintf(
        'select stddev(fsz) as fstd '.
        'from ( '.
        '  select round('.
        '    substring_index( '.
        '      substring_index(qcdata, ",", 1), ":", -1)/1024.0) as fsz '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and qcdata is not null '.
        '  and s.name="ecg" '.
        ') as t '.
        'where fsz>0', $this->rank);

      $res = $db->get_row( $sql );
      $stdev = $res['fstd'];
      $filesize_min = max(intval(($avg - $this->standard_deviation_scale*$stdev)*1024),0);
      $filesize_max = intval(($avg + $this->standard_deviation_scale*$stdev))*1024;
    }

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(substring_index(substring_index(qcdata,",",1),":",-1)<%d,1,0))) as total_filesize_sub, ',$filesize_min);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(substring_index(substring_index(qcdata,",",1),":",-1) between %d and %d,1,0))) as total_filesize_par, ',$filesize_min,$filesize_max);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(substring_index(substring_index(qcdata,",",1),":",-1)>%d,1,0))) as total_filesize_sup, ',$filesize_max);

    $sql .= 'sum(if(qcdata is null, 0, 1-trim("}" from substring_index(qcdata,":",-1)))) as total_poor_quality, ';

    $sql .= $this->get_main_query();

    $res = $db->get_all( $sql );
    if(false===$res || !is_array($res))
    {
      echo sprintf('error: failed query: %s', $db->get_last_error());
      echo $sql;
      die();
    }
    $this->data = $res;

    $this->page_explanation = array();
    if('mode'==$this->statistic)
    {
      $this->page_explanation[]=sprintf('filesize sub: size < %d (min + 0.5 x (mode - min))', $filesize_min);
      $this->page_explanation[]=sprintf('filesize par: %d <= size <= %d', $filesize_min, $filesize_max);
      $this->page_explanation[]=sprintf('filesize sup: size > %d (mode + 0.5 x (max - mode))', $filesize_max);
    }
    else
    {
      $this->page_explanation[]=sprintf('filesize sub: size < %d (mean - %s x SD)', $filesize_min, $this->standard_deviation_scale);
      $this->page_explanation[]=sprintf('filesize par: %d <= size <= %d', $filesize_min, $filesize_max);
      $this->page_explanation[]=sprintf('filesize sup: size > %d (mean + %s x SD)', $filesize_max, $this->standard_deviation_scale);
    }
    $this->page_explanation[]="'poor quality' as indicated by ECG diagnosis output text";
  }

  private $statistic;

  private $standard_deviation_scale;
}


$ecg = new ecg_generator('ecg', $rank, $begin_date, $end_date);

$qc_keys=array('total_filesize_sub','total_filesize_par','total_filesize_sup');
$percent_keys = array('total_poor_quality','total_skip','total_unexplained_missing','total_contraindicated');

$ecg->set_indicator_keys($qc_keys);
$ecg->set_percent_keys($percent_keys);
$ecg->set_statistic($stat);
$ecg->set_standard_deviation_scale(2);

$ecg->build_table_data();

$qc_keys[]='total_poor_quality';

$ecg->set_indicator_keys($qc_keys);
$ecg->set_page_stage('ECG');

echo $ecg->build_table_html();
