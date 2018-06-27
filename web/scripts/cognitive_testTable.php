<?php
require_once 'table_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

class cognitive_test_generator extends table_generator
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
        '  select '.
        '    trim("}" from '.
        '      substring_index(qcdata, ":", -1)) as fsz '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and qcdata is not null '.
        '  and s.name="%s" '.
        ') as t '.
        'where fsz>0 '.
        'group by fsz '.
        'order by freq desc limit 1', $this->rank, $this->name);

      $res = $db->get_row( $sql );
      $mode = $res['fsz'];

      $sql = sprintf(
        'select min(fsz) as minsz, max(fsz) as maxsz '.
        'from ( '.
        '  select '.
        '    trim("}" from substring_index(qcdata, ":", -1)) as fsz '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and qcdata is not null '.
        '  and s.name="%s" '.
        ') as t '.
        'where fsz>0', $this->rank, $this->name);

      $res = $db->get_row( $sql );
      $minsz = $res['minsz'];
      $maxsz = $res['maxsz'];
      $filesize_min = max(intval($minsz + 0.5*($mode-$minsz)),0);
      $filesize_max = intval($mode + 0.5*($maxsz-$mode));
    }
    else
    {
      $avg=0;
      $stdev=0;
      $sql = sprintf(
        'select avg(fsz) as favg '.
        'from ( '.
        '  select trim("}" from substring_index(qcdata, ":", -1)) as fsz '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and qcdata is not null '.
        '  and s.name="%s" '.
        ') as t '.
        'where fsz>0', $this->rank, $this->name);

      $res = $db->get_row( $sql );
      $avg = $res['favg'];

      $sql = sprintf(
        'select stddev(fsz) as fstd '.
        'from ( '.
        '  select trim("}" from substring_index(qcdata, ":", -1)) as fsz '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and qcdata is not null '.
        '  and s.name="%s" '.
        ') as t '.
        'where fsz>0', $this->rank, $this->name);

      $res = $db->get_row( $sql );
      $stdev = $res['fstd'];
      $filesize_min = max(intval($avg - $this->standard_deviation_scale*$stdev),0);
      $filesize_max = intval($avg + $this->standard_deviation_scale*$stdev);
    }

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(trim("}" from substring_index(qcdata,":",-1))<%d,1,0))) as total_filesize_sub, ',$filesize_min);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(trim("}" from substring_index(qcdata,":",-1)) between %d and %d,1,0))) as total_filesize_par, ',$filesize_min,$filesize_max);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(trim("}" from substring_index(qcdata,":",-1))>%d,1,0))) as total_filesize_sup, ',$filesize_max);

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
  }

  private $statistic;

  private $standard_deviation_scale;
}

$cognitive_test = new cognitive_test_generator(
  'cognitive_test', $rank, $begin_date, $end_date);

$qc_keys=array('total_filesize_sub','total_filesize_par','total_filesize_sup');
$percent_keys = array('total_skip','total_unexplained_missing','total_contraindicated');

$cognitive_test->set_indicator_keys($qc_keys);
$cognitive_test->set_percent_keys($percent_keys);
$cognitive_test->set_statistic($stat);
$cognitive_test->set_standard_deviation_scale(2);

$cognitive_test->build_table_data();

$cognitive_test->set_page_stage('COGNITIVE TEST');

echo $cognitive_test->build_table_html();
