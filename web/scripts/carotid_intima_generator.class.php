<?php
require_once 'table_generator.class.php';

class carotid_intima_generator extends table_generator
{
  public function __construct($_stage, $_rank, $_begin_date = null, $_end_date = null)
  {
    parent::__construct($_stage, $_rank, $_begin_date, $_end_date);

    $this->indicator_keys = array('total_filesize_sub','total_filesize_par','total_filesize_sup');
    $this->statistic = 'mean'; // default
    $this->standard_deviation_scale= 2;  // default
    $this->set_file_type( 'cineloop' );
  }

  public function set_file_type( $_file_type )
  {
    if($this->file_type != $_file_type &&
      in_array($_file_type, array('cineloop','sr','still')))
    {
      $this->file_type = $_file_type;
      switch($this->file_type)
      {
        case 'cineloop':
          $this->page_stage = 'CAROTID INTIMA CINELOOP';
          $this->file_scale = 1048576.0;
          $this->percent_keys = array(
            'total_left_cineloop_only','total_right_cineloop_only',
            'total_both_cineloop','total_skip','total_unexplained_missing','total_contraindicated');
          $this->file_list = array(
            1=>array(3,4,5,8,9,10),
            2=>array(1,6),
            3=>array(1,6));
          break;
        case 'sr':
          $this->page_stage = 'CAROTID INTIMA SR REPORT';
          $this->file_scale = 1024.0;
          $this->percent_keys = array(
            'total_left_sr_only','total_right_sr_only',
            'total_both_sr','total_skip','total_unexplained_missing','total_contraindicated');
          $this->file_list = array(
            1=>array(2,7),
            2=>array(2,7),
            3=>array(2,7));
          break;
        case 'still':
          $this->page_stage = 'CAROTID INTIMA STILL';
          $this->file_scale = 1024.0;
          $this->percent_keys = array(
            'total_left_still_only','total_right_still_only',
            'total_both_still','total_skip','total_unexplained_missing','total_contraindicated');
          $this->file_list = array(
            1=>array(1,6),
            2=>array(3,4,5,8,9,10),
            3=>array(3,4,5,8,9,10));
          break;
        default:
          break;
      }
    }
  }

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

    $union_sql = array();
    foreach($this->file_list[$this->rank] as $item)
    {
      $union_sql[] =
       sprintf(
          '  ( '.
          '    select '.
          '      round( '.
          '        cast( trim("}" from '.
          '          substring_index( '.
          '            substring_index( '.
          '              qcdata, ",", %d ), ":", -1)) as unsigned)/%s,0) as fsz '.
          '    from interview i'.
          '    join stage s on i.id=s.interview_id'.
          '    where rank=%d'.
          '    and qcdata is not null'.
          '    and s.name="%s" '.
          '  ) ', $item, $this->file_scale, $this->rank, $this->name );
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
      $filesize_min = max(intval(($minsz + 0.5*($mode-$minsz))*$this->file_scale),0);
      $filesize_max = intval(($mode + 0.5*($maxsz-$mode))*$this->file_scale);
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
      $filesize_min = max(intval(($avg - $this->standard_deviation_scale*$stdev)*$this->file_scale),0);
      $filesize_max = intval(($avg + $this->standard_deviation_scale*$stdev)*$this->file_scale);
    }

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    $sum_sql = array();
    foreach($this->file_list[$this->rank] as $item)
    {
      $sum_sql[] = sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(cast(trim("}" from substring_index(substring_index(qcdata,",",%d),":",-1)) as unsigned)<%d,1,0))) ', $item, $filesize_min);
    }
    $sql .= implode( '+', $sum_sql ) . ' as total_filesize_sub, ';

    $sum_sql = array();
    foreach($this->file_list[$this->rank] as $item)
    {
      $sum_sql[] = sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(cast(trim("}" from substring_index(substring_index(qcdata,",",%d),":",-1)) as unsigned) between %d and %d,1,0)))',
         $item, $filesize_min, $filesize_max);
    }
    $sql .= implode( ' + ', $sum_sql ) . ' as total_filesize_par, ';

    $sum_sql = array();
    foreach($this->file_list[$this->rank] as $item)
    {
      $sum_sql[] = sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(cast(trim("}" from substring_index(substring_index(qcdata,",",%d),":",-1)) as unsigned)>%d,1,0)))', $item, $filesize_max);
    }
    $sql .= implode( ' + ', $sum_sql ) . ' as total_filesize_sup, ';

    $file_left = array_slice($this->file_list[$this->rank], 0, count($this->file_list[$this->rank])/2);
    $file_right = array_slice($this->file_list[$this->rank], count($this->file_list[$this->rank])/2);

    $and_sql = array();
    foreach($file_left as $index)
    {
      $and_sql[] = sprintf('cast(trim("}" from substring_index(substring_index(qcdata,",",%d),":",-1)) as signed)>0', $index);
    }
    foreach($file_right as $index)
    {
      $and_sql[] = sprintf('cast(trim("}" from substring_index(substring_index(qcdata,",",%d),":",-1)) as signed)=0', $index);
    }
    $sql .= 'sum(if(qcdata is null, 0, if( ' .
             implode( ' and ', $and_sql ) . ',1,0))) as total_left_' . $this->file_type . '_only, ';

    $and_sql = array();
    foreach($file_right as $index)
    {
      $and_sql[] = sprintf('cast(trim("}" from substring_index(substring_index(qcdata,",",%d),":",-1)) as signed)>0', $index);
    }
    foreach($file_left as $index)
    {
      $and_sql[] = sprintf('cast(trim("}" from substring_index(substring_index(qcdata,",",%d),":",-1)) as signed)=0', $index);
    }
    $sql .= 'sum(if(qcdata is null, 0, if( ' .
             implode( ' and ', $and_sql ) . ',1,0))) as total_right_' . $this->file_type . '_only, ';

    $and_sql = array();
    foreach($file_right as $index)
    {
      $and_sql[] = sprintf('cast(trim("}" from substring_index(substring_index(qcdata,",",%d),":",-1)) as signed)>0', $index);
    }
    foreach($file_left as $index)
    {
      $and_sql[] = sprintf('cast(trim("}" from substring_index(substring_index(qcdata,",",%d),":",-1)) as signed)>0', $index);
    }
    $sql .= 'sum(if(qcdata is null, 0, if( ' .
             implode( ' and ', $and_sql ) . ',1,0))) as total_both_' . $this->file_type . ', ';

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
    $this->page_explanation[]=sprintf('presence of all left %s(s) only',$this->file_type);
    $this->page_explanation[]=sprintf('presence of all left %s(s) only',$this->file_type);
    $this->page_explanation[]=sprintf('presence of all right %s(s) only',$this->file_type);
    $this->page_explanation[]=sprintf('presence of all %s(s)',$this->file_type);
    $this->page_explanation[]=sprintf('presence of all right %s(s) only',$this->file_type);
    $this->page_explanation[]=sprintf('presence of all %s(s)',$this->file_type);
  }

  public function build_table_data()
  {
    parent::build_table_data();

    $this->indicator_keys[] = sprintf('total_left_%s_only',$this->file_type);
    $this->indicator_keys[] = sprintf('total_right_%s_only',$this->file_type);
    $this->indicator_keys[] = sprintf('total_both_%s',$this->file_type);
  }

  private $statistic;

  private $standard_deviation_scale;

  private $file_type;

  private $file_scale;

  private $file_list;
}
