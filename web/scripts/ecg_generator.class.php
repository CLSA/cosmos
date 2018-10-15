<?php
require_once 'table_generator.class.php';

class ecg_generator extends table_generator
{
  public function __construct($_stage, $_rank, $_begin_date = null, $_end_date = null)
  {
    parent::__construct($_stage, $_rank, $_begin_date, $_end_date);

    $this->indicator_keys = array('total_filesize_sub','total_filesize_par','total_filesize_sup');
    $this->percent_keys = array('total_poor_quality','total_skip','total_unexplained_missing','total_contraindicated');
    $this->statistic = 'mean'; // default
    $this->standard_deviation_scale= 2;  // default
    $this->file_scale = 1024.0;
    $this->page_stage ='ECG';
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
        '    cast(substring_index( '.
        '      substring_index(qcdata, ",", 1 ), ":", -1) as decimal)/%s,0) as fsz '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and qcdata is not null '.
        '  and s.name="%s" '.
        ') as t '.
        'where fsz>0 '.
        'group by fsz '.
        'order by freq desc limit 1', $this->file_scale, $this->rank, $this->name);

      $res = $db->get_row( $sql );
      $mode = $res['fsz'];

      $sql = sprintf(
        'select min(fsz) as minsz, max(fsz) as maxsz '.
        'from ( '.
        '  select round('.
        '    cast(substring_index( '.
        '      substring_index(qcdata, ",", 1), ":", -1) as decimal)/%s) as fsz '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and qcdata is not null '.
        '  and s.name="%s" '.
        ') as t '.
        'where fsz>0', $this->file_scale, $this->rank, $this->name);

      $res = $db->get_row( $sql );
      $minsz = $res['minsz'];
      $maxsz = $res['maxsz'];
      $filesize_min = max(intval(($minsz + 0.5*($mode-$minsz))*$this->file_scale),0);
      $filesize_max = intval(($mode + 0.5*($maxsz-$mode))*$this->file_scale);
    }
    else
    {
      $avg=0;
      $stdev=0;
      $sql = sprintf(
        'select avg(fsz) as favg, stddev(fsz) as fstd  '.
        'from ( '.
        '  select round( '.
        '    cast(substring_index( '.
        '      substring_index(qcdata, ",", 1 ), ":", -1) as decimal)/%s,0) as fsz '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and qcdata is not null '.
        '  and s.name="%s" '.
        ') as t '.
        'where fsz>0', $this->file_scale, $this->rank, $this->name);

      $res = $db->get_row( $sql );
      $res = $db->get_row( $sql );
      if( false === $res )
      {
        echo sprintf('failed to get test time data: %s', $db->get_last_error() );
        echo $sql;
        die();
      }

      $avg = $res['favg'];
      $stdev = $res['fstd'];

      $filesize_min = max(intval(($avg - $this->standard_deviation_scale*$stdev)*$this->file_scale),0);
      $filesize_max = intval(($avg + $this->standard_deviation_scale*$stdev)*$this->file_scale);
    }

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(cast(substring_index(substring_index(qcdata,",",1),":",-1) as signed)<%d,1,0))) as total_filesize_sub, ',$filesize_min);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(cast(substring_index(substring_index(qcdata,",",1),":",-1) as signed) between %d and %d,1,0))) as total_filesize_par, ',$filesize_min,$filesize_max);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(cast(substring_index(substring_index(qcdata,",",1),":",-1) as signed)>%d,1,0))) as total_filesize_sup, ',$filesize_max);

    $sql .= 'sum(if(qcdata is null, 0, 1-cast(trim("}" from substring_index(qcdata,":",-1)) as signed))) as total_poor_quality, ';

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
      $this->page_explanation[]=sprintf('subpar filesize: size < %d (min + 0.5 x (mode - min))', $filesize_min);
      $this->page_explanation[]=sprintf('par filesize: %d <= size <= %d', $filesize_min, $filesize_max);
      $this->page_explanation[]=sprintf('above par filesize: size > %d (mode + 0.5 x (max - mode))', $filesize_max);
    }
    else
    {
      $this->page_explanation[]=sprintf('subpar filesize: size < %d (mean - %s x SD)', $filesize_min, $this->standard_deviation_scale);
      $this->page_explanation[]=sprintf('par filesize: %d <= size <= %d', $filesize_min, $filesize_max);
      $this->page_explanation[]=sprintf('above par filesize: size > %d (mean + %s x SD)', $filesize_max, $this->standard_deviation_scale);
    }
    $this->page_explanation[]="'poor quality' as indicated by ECG diagnosis output text";
  }

  public function build_table_data()
  {
    parent::build_table_data();

    $this->indicator_keys[] = 'total_poor_quality';
  }

  private $file_scale;
}
