<?php
require_once 'table_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

class carotid_intima_sr_generator extends table_generator
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

    // structured_report image sizes
    $file_list = array(
      1=>array(2,7),
      2=>array(2,7),
      3=>array(2,7));

    $union_sql = array();
    foreach($file_list[$this->rank] as $item)
    {
      $union_sql[] =
       sprintf(
          '  ( '.
          '    select '.
          '      round( '.
          '        trim("}" from '.
          '          substring_index( '.
          '            substring_index( '.
          '              qcdata, ",", %d ), ":", -1))/1024.0,0) as fsz '.
          '    from interview i'.
          '    join stage s on i.id=s.interview_id'.
          '    where rank=%d'.
          '    and qcdata is not null'.
          '    and s.name="%s" '.
          '  ) ', $item, $this->rank, $this->name );
    }

    $filesize_min = 0;
    $filesize_max = 0;
    if( 'mode' == $this->statistic )
    {
      $minsz=0;
      $maxsz=0;
      $mode=0;
      $sql = 'select fsz, count(fsz) as freq from ( '.
              implode( ' union all ', $union_sql ) .
             ' ) as t where fsz>0';

      $res = $db->get_row( $sql );
      $mode = $res['fsz'];
      $sql = 'select min(fsz) as minsz, max(fsz) as maxsz from ( '.
              implode( ' union all ', $union_sql ) .
             ' ) as t where fsz>0';

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
      $sql = 'select avg(fsz) as favg from ( '.
              implode( ' union all ', $union_sql ) .
             ' ) as t where fsz>0';

      $res = $db->get_row( $sql );
      $avg = $res['favg'];

      $sql = 'select stddev(fsz) as fstd from ( '.
              implode( ' union all ', $union_sql ) .
             ' ) as t where fsz>0';

      $res = $db->get_row( $sql );
      $stdev = $res['fstd'];
      $filesize_min = max(intval(($avg - $this->standard_deviation_scale*$stdev)*1024),0);
      $filesize_max = intval(($avg + $this->standard_deviation_scale*$stdev)*1024);
    }

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    $sum_sql = array();
    foreach($file_list[$this->rank] as $item)
    {
      $sum_sql[] = sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(trim("}" from substring_index(substring_index(qcdata,",",%d),":",-1))<%d,1,0))) ', $item, $filesize_min);
    }
    $sql .= implode( '+', $sum_sql ) . ' as total_filesize_sub, ';

    $sum_sql = array();
    foreach($file_list[$this->rank] as $item)
    {
      $sum_sql[] = sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(trim("}" from substring_index(substring_index(qcdata,",",%d),":",-1)) between %d and %d,1,0)))',
         $item, $filesize_min, $filesize_max);
    }
    $sql .= implode( ' + ', $sum_sql ) . ' as total_filesize_par, ';

    $sum_sql = array();
    foreach($file_list[$this->rank] as $item)
    {
      $sum_sql[] = sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(trim("}" from substring_index(substring_index(qcdata,",",%d),":",-1))>%d,1,0)))', $item, $filesize_max);
    }
    $sql .= implode( ' + ', $sum_sql ) . ' as total_filesize_sup, ';

    $file_left = array_slice($file_list[$this->rank], 0, count($file_list[$this->rank])/2);
    $file_right = array_slice($file_list[$this->rank], count($file_list[$this->rank])/2);

    $and_sql = array();
    foreach($file_left as $index)
    {
      $and_sql[] = sprintf('trim("}" from substring_index(substring_index(qcdata,",",%d),":",-1))>0', $index);
    }
    foreach($file_right as $index)
    {
      $and_sql[] = sprintf('trim("}" from substring_index(substring_index(qcdata,",",%d),":",-1))=0', $index);
    }
    $sql .= 'sum(if(qcdata is null, 0, if( ' .
             implode( ' and ', $and_sql ) . ',1,0))) as total_left_structured_report_only, ';

    $and_sql = array();
    foreach($file_right as $index)
    {
      $and_sql[] = sprintf('trim("}" from substring_index(substring_index(qcdata,",",%d),":",-1))>0', $index);
    }
    foreach($file_left as $index)
    {
      $and_sql[] = sprintf('trim("}" from substring_index(substring_index(qcdata,",",%d),":",-1))=0', $index);
    }
    $sql .= 'sum(if(qcdata is null, 0, if( ' .
             implode( ' and ', $and_sql ) . ',1,0))) as total_right_structured_report_only, ';

    $and_sql = array();
    foreach($file_right as $index)
    {
      $and_sql[] = sprintf('trim("}" from substring_index(substring_index(qcdata,",",%d),":",-1))>0', $index);
    }
    foreach($file_left as $index)
    {
      $and_sql[] = sprintf('trim("}" from substring_index(substring_index(qcdata,",",%d),":",-1))>0', $index);
    }
    $sql .= 'sum(if(qcdata is null, 0, if( ' .
             implode( ' and ', $and_sql ) . ',1,0))) as total_both_structured_report, ';

    $sql .= $this->get_main_query();

    $res = $db->get_all( $sql );
    if(false===$res || !is_array($res))
    {
      echo sprintf('error: failed query: %s', $db->get_last_error());
      echo $sql;
      die();
    }
    $this->data = $res;

    $this->page_explanation=array();
    if('mode'==$this->statistic)
    {
      $this->page_explanation[]=sprintf('filesize sub: size < %d (min + 0.5 x (mode - min))',$filesize_min);
      $this->page_explanation[]=sprintf('filesize par: %d <= size <= %d',$filesize_min,$filesize_max);
      $this->page_explanation[]=sprintf('filesize sup: size > %d (mode + 0.5 x (max - mode))',$filesize_max);
    }
    else
    {
      $this->page_explanation[]=sprintf('filesize sub: size < %d (mean - %s x SD)',$filesize_min,$this->standard_deviation_scale);
      $this->page_explanation[]=sprintf('filesize par: %d <= size <= %d',$filesize_min,$filesize_max);
      $this->page_explanation[]=sprintf('filesize sup: size > %d (mean + %s x SD)',$filesize_max,$this->standard_deviation_scale);
    }
    $this->page_explanation[]=sprintf('presence of all left SR(s) only');
    $this->page_explanation[]=sprintf('presence of all left SR(s) only');
    $this->page_explanation[]=sprintf('presence of all right SR(s) only');
    $this->page_explanation[]=sprintf('presence of all SR(s)');
    $this->page_explanation[]=sprintf('presence of all right SR(s) only');
    $this->page_explanation[]=sprintf('presence of all SR(s)');
  }

  private $statistic;

  private $standard_deviation_scale;
}

$carotid_intima = new carotid_intima_sr_generator('carotid_intima', $rank, $begin_date, $end_date);

$qc_keys=array(
  'total_filesize_sub','total_filesize_par','total_filesize_sup'
);
$percent_keys = array('total_left_structured_report_only','total_right_structured_report_only',
  'total_both_structured_report','total_skip','total_unexplained_missing','total_contraindicated');

$carotid_intima->set_indicator_keys($qc_keys);
$carotid_intima->set_percent_keys($percent_keys);

$carotid_intima->set_statistic($stat);
$carotid_intima->set_standard_deviation_scale(1);

$carotid_intima->build_table_data();

$qc_keys[] = 'total_left_structured_report_only';
$qc_keys[] = 'total_right_structured_report_only';
$qc_keys[] = 'total_both_structured_report';
$carotid_intima->set_indicator_keys($qc_keys);

$carotid_intima->set_page_stage('CAROTID INTIMA STRUCTURED REPORT');

echo $carotid_intima->build_table_html();
