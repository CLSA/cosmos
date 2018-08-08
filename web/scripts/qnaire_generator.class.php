<?php
require_once 'table_generator.class.php';

class qnaire_generator extends table_generator
{
  public function __construct($_stage, $_rank, $_begin_date = null, $_end_date = null)
  {
    parent::__construct($_stage, $_rank, $_begin_date, $_end_date);

    $this->indicator_keys = array('total_complete_sub','total_complete_par','total_complete_sup');
    $this->standard_deviation_scale = 2;  // default
    $this->statistic = 'mean';            // default
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

    $complete_min=0;
    $complete_max=0;
    if('mode' == $this->statistic)
    {
      $min_perc=0;
      $max_perc=0;
      $mode=0;
      $sql = sprintf(
        'select f_perc, count(f_perc) as freq '.
        'from ( '.
        '  select cast('.
        '    trim("}" from '.
        '      substring_index(qcdata, ":", -1)) as signed) as f_perc '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and qcdata is not null '.
        '  and s.name="%s" '.
        ') as t '.
        'group by f_perc '.
        'order by freq desc limit 1', $this->rank, $this->name);

      $res = $db->get_row( $sql );
      $mode = $res['f_perc'];

      $sql = sprintf(
        'select min(f_perc) as min_perc, max(f_perc) as max_perc '.
        'from ( '.
        '  select cast('.
        '    trim("}" from substring_index(qcdata, ":", -1)) as signed) as f_perc '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and qcdata is not null '.
        '  and s.name="%s" '.
        ') as t',  $this->rank, $this->name);

      $res = $db->get_row( $sql );
      $min_perc = $res['min_perc'];
      $max_perc = $res['max_perc'];
      $complete_min = max(intval(($min_perc + 0.5*($mode-$min_perc))),0);
      $complete_max = intval(($mode + 0.5*($max_perc-$mode)));
    }
    else
    {
      $avg=0;
      $stdev=0;
      $sql = sprintf(
        'select avg(f_perc) as favg '.
        'from ( '.
        '  select cast(trim("}" from substring_index(qcdata, ":", -1)) as signed) as f_perc '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and qcdata is not null '.
        '  and s.name="%s" '.
        ') as t', $this->rank, $this->name);

      $res = $db->get_row( $sql );
      $avg = $res['favg'];

      $sql = sprintf(
        'select stddev(f_perc) as fstd '.
        'from ( '.
        '  select cast(trim("}" from substring_index(qcdata, ":", -1)) as signed) as f_perc '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and qcdata is not null '.
        '  and s.name="%s" '.
        ') as t', $this->rank, $this->name);

      $res = $db->get_row( $sql );
      $stdev = $res['fstd'];
      $complete_min = max(intval(($avg - $this->standard_deviation_scale*$stdev)),0);
      $complete_max = intval(($avg + $this->standard_deviation_scale*$stdev));
    }

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(cast(trim("}" from substring_index(qcdata,":",-1)) as signed)<%d,1,0))) as total_complete_sub, ',$complete_min);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(cast(trim("}" from substring_index(qcdata,":",-1)) as signed) between %d and %d,1,0))) as total_complete_par, ',$complete_min,$complete_max);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(cast( trim("}" from substring_index(qcdata,":",-1)) as signed)>%d,1,0))) as total_complete_sup, ',$complete_max);

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
      $this->page_explanation[]=sprintf('percent complete sub: size < %d (min + 0.5 x (mode - min))', $complete_min);
      $this->page_explanation[]=sprintf('percent complete par: %d <= size <= %d', $complete_min, $complete_max);
      $this->page_explanation[]=sprintf('percent complete sup: size > %d (mode + 0.5 x (max - mode))', $complete_max);
    }
    else
    {
      $this->page_explanation[]=sprintf('percent complete sub: size < %d (mean - %s x SD)', $complete_min, $this->standard_deviation_scale);
      $this->page_explanation[]=sprintf('percent complete par: %d <= size <= %d', $complete_min, $complete_max);
      $this->page_explanation[]=sprintf('percent complete sup: size > %d (mean + %s x SD)', $complete_max, $this->standard_deviation_scale);
    }
  }

  private $statistic;

  private $standard_deviation_scale;
}
