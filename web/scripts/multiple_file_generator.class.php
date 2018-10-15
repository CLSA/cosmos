<?php
require_once 'table_generator.class.php';

class multiple_file_generator extends table_generator
{
  public function __construct($_stage, $_rank, $_begin_date = null, $_end_date = null)
  {
    parent::__construct($_stage, $_rank, $_begin_date, $_end_date);

    $this->indicator_keys = array();
    $this->standard_deviation_scale = 2;  // default
    $this->statistic = 'mean';            // default
    $this->file_scale = 1.0;
    $this->file_list = null;
  }

  public function set_file_list( $_list )
  {
    $this->file_list = $_list;
  }

  public function set_file_scale( $_scale )
  {
    if( 0 < $_scale )
      $this->file_scale = $_scale;
  }

  protected function build_data()
  {
    global $db;

    $file_keys = array_keys($this->file_list);
    $file_count = count($file_keys);
    $filesize_min = array_combine($file_keys,array_fill(0,$file_count,0));
    $filesize_max = array_combine($file_keys,array_fill(0,$file_count,0));
    foreach($this->file_list as $key=>$index)
    {
      $this->indicator_keys[]=sprintf('total_%s_sub',$key);
      $this->indicator_keys[]=sprintf('total_%s_par',$key);
      $this->indicator_keys[]=sprintf('total_%s_sup',$key);

      if('mode' == $this->statistic)
      {
        $minsz=0;
        $maxsz=0;
        $mode=0;
        $sql = sprintf(
          'select fsz, count(fsz) as freq '.
          'from ( '.
          '  select '.
          '   round( '.
          '    cast(substring_index( '.
          '      substring_index(qcdata,",",%d),":",-1) as unsigned)/%s,0) as fsz '.
          '  from interview i '.
          '  join stage s on i.id=s.interview_id '.
          '  where rank=%d '.
          '  and qcdata is not null '.
          '  and s.name="%s" '.
          ') as t '.
          'where fsz>0 '.
          'group by fsz '.
          'order by freq desc limit 1', $index, $this->file_scale, $this->rank, $this->name);

        $res = $db->get_row( $sql );
        if( false === $res )
        {
          echo sprintf('failed to get file size data: %s', $db->get_last_error() );
          echo $sql;
          die();
        }
        $mode = $res['fsz'];

        $sql = sprintf(
          'select min(fsz) as minsz, max(fsz) as maxsz '.
          'from ( '.
          '  select '.
          '    round( '.
          '      cast(sub_string_index( '.
          '       substring_index(qcdata,",",%d),":",-1) as unsigned)/%s,0) as fsz '.
          '  from interview i '.
          '  join stage s on i.id=s.interview_id '.
          '  where rank=%d '.
          '  and qcdata is not null '.
          '  and s.name="%s" '.
          ') as t '.
          'where fsz>0', $index, $this->file_scale, $this->rank, $this->name);

        $res = $db->get_row( $sql );
        if( false === $res )
        {
          echo sprintf('failed to get file size data: %s', $db->get_last_error() );
          echo $sql;
          die();
        }
        $minsz = $res['minsz'];
        $maxsz = $res['maxsz'];
        $filesize_min[$key] = max(intval(($minsz + 0.5*($mode-$minsz))*$this->file_scale),0);
        $filesize_max[$key] = intval(($mode + 0.5*($maxsz-$mode))*$this->file_scale);
      }
      else
      {
        $avg=0;
        $stdev=0;
        $sql = sprintf(
          'select avg(fsz) as favg, stddev(fsz) as fstd '.
          'from ( '.
          '  select '.
          '   round( '.
          '    cast(substring_index( '.
          '      substring_index(qcdata,",",%d),":",-1) as unsigned)/%s,0) as fsz '.
          '  from interview i '.
          '  join stage s on i.id=s.interview_id '.
          '  where rank=%d '.
          '  and qcdata is not null '.
          '  and s.name="%s" '.
          ') as t '.
          'where fsz>0', $index, $this->file_scale, $this->rank, $this->name);

        $res = $db->get_row( $sql );
        if( false === $res )
        {
          echo sprintf('failed to get file size data: %s', $db->get_last_error() );
          echo $sql;
          die();
        }
        $avg = $res['favg'];
        $stdev = $res['fstd'];
        $filesize_min[$key] = max(intval(($avg - $this->standard_deviation_scale*$stdev)*$this->file_scale),0);
        $filesize_max[$key] = intval(($avg + $this->standard_deviation_scale*$stdev)*$this->file_scale);
      }
    }
    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    foreach($this->file_list as $key=>$index)
    {
      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(cast(substring_index(substring_index(qcdata,",",%d),":",-1) as unsigned)<%d,1,0))) as total_%s_sub, ',
        $index, $filesize_min[$key], $key);

      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(cast(substring_index(substring_index(qcdata,",",%d),":",-1) as unsigned) between %d and %d,1,0))) as total_%s_par, ',
        $index, $filesize_min[$key], $filesize_max[$key], $key);

      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(cast(substring_index(substring_index(qcdata,",",%d),":",-1) as unsigned)>%d,1,0))) as total_%s_sup, ',
        $index, $filesize_max[$key], $key);
    }
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
    foreach($this->file_list as $key=>$index)
    {
      $this->page_explanation[]=$key;
      if('mode'==$this->statistic)
      {
        $this->page_explanation[]=sprintf('subpar filesize: size < %d (min + 0.5 x (mode - min))', $filesize_min[$key]);
        $this->page_explanation[]=sprintf('par filesize: %d <= size <= %d', $filesize_min[$key], $filesize_max[$key]);
        $this->page_explanation[]=sprintf('above par filesize: size > %d (mode + 0.5 x (max - mode))', $filesize_max[$key]);
      }
      else
      {
        $this->page_explanation[]=sprintf('subpar filesize: size < %d (mean - %s x SD)', $filesize_min[$key], $this->standard_deviation_scale);
        $this->page_explanation[]=sprintf('par filesize: %d <= size <= %d', $filesize_min[$key], $filesize_max[$key]);
        $this->page_explanation[]=sprintf('above par filesize: size > %d (mean + %s x SD)', $filesize_max[$key], $this->standard_deviation_scale);
      }
    }
  }

  private $file_scale;

  private $file_list;
}
