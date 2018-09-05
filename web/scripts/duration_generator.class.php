<?php

require_once 'table_generator.class.php';

class duration_generator extends table_generator
{
  public function __construct($_stage, $_rank, $_begin_date = null, $_end_date = null)
  {
    global $db;
    parent::__construct($_stage, $_rank, $_begin_date, $_end_date);

    $this->indicator_keys = array('total_time_sub','total_time_par','total_time_sup');
    $this->standard_deviation_scale = 1;
    $this->statistic = 'mean';            // default
    $this->threshold = 60; // default 60 minutes

    // what type of duration data is here?
    $sql = sprintf(
      'select duration from stage s '.
      'join interview i on i.id=s.interview_id '.
      'where rank=%d '.
      'and s.name="%s" '.
      'and duration is not null '.
      'limit 1',$this->rank,$this->name);
    $res = $db->get_one($sql);
    if(false===$res)
    {
      echo sprintf('error: failed query: %s', $db->get_last_error());
      echo $sql;
      die();
    }
    $res = explode(',',trim($res,'{}'));
    $this->module_keys =array();
    foreach($res as $value)
    {
      $value=explode(':',$value);
      $this->module_keys[]=array_shift($value);
    }
    $this->has_module=1<count($this->module_keys);
    if($this->has_module)
    {
      $this->indicator_keys = array_merge($this->indicator_keys,
        array('total_module_sub','total_module_par','total_module_sup',
        'total_lag_sub','total_lag_par','total_lag_sup'));
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

  public function set_threshold($_thresh)
  {
    $this->threshold = $_thresh;
  }

  protected function build_data()
  {
    global $db;

    $sql = sprintf(
        'select avg(d_time) as d_avg, stddev(d_time) as d_std, min(d_time) as d_min, max(d_time) as d_max '.
        'from ( '.
        '  select cast(substring_index(substring_index(duration,",",1),":",-1) as decimal(10,3)) as d_time '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and duration is not null '.
        '  and s.name="%s" '.
        ') as t '.
        'where d_time>0.0 and d_time<=%s ', $this->rank, $this->name, $this->threshold);

    $res = $db->get_row( $sql );

    $stage_avg = $res['d_avg'];
    $stage_stdev = $res['d_std'];
    $stage_min = $res['d_min'];
    $stage_max= $res['d_max'];

    $stage_time_min = max(($stage_avg - $this->standard_deviation_scale*$stage_stdev),0);
    $stage_time_max = $stage_avg + $this->standard_deviation_scale*$stage_stdev;

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    $sql .= sprintf(
      'sum(if(duration is null, 0, '.
      'if(cast(substring_index(substring_index(duration,",",1),":",-1) as decimal(10,3))<%s,1,0))) as total_time_sub, ',$stage_time_min);

    $sql .= sprintf(
      'sum(if(duration is null, 0, '.
      'if(cast(substring_index(substring_index(duration,",",1),":",-1) as decimal(10,3)) between %s and %s,1,0))) as total_time_par, ',$stage_time_min,$stage_time_max);

    $sql .= sprintf(
      'sum(if(duration is null, 0, '.
      'if(cast(substring_index(substring_index(duration,",",1),":",-1) as decimal(10,3))>%s,1,0))) as total_time_sup, ',$stage_time_max);

    $this->page_explanation = array();
    $this->page_explanation[]=sprintf('time [min, max] : [%s, %s] ',
      util::time_to_label($stage_min), util::time_to_label($stage_max));
    $this->page_explanation[]=sprintf('time [avg, stdev] : [%s, %s] ',
      util::time_to_label($stage_avg), util::time_to_label($stage_stdev));
    $this->page_explanation[]=sprintf('time threshold : %s min', $this->threshold);
    $this->page_explanation[]=sprintf('subpar time: time < %s (mean - %s x SD) min', $stage_time_min, $this->standard_deviation_scale);
    $this->page_explanation[]=sprintf('par time: %s <= time <= %d min', $stage_time_min, $stage_time_max);
    $this->page_explanation[]=sprintf('above par time: time > %s (mean + %s x SD) min', $stage_time_max, $this->standard_deviation_scale);

    if($this->has_module)
    {
      $module_sql = sprintf(
        'select avg(d_time) as d_avg, stddev(d_time) as d_std, min(d_time) as d_min, max(d_time) as d_max '.
        'from ( '.
        '  select cast(trim("}" from substring_index(duration,":",-1)) as decimal(10,3)) as d_time '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and duration is not null '.
        '  and s.name="%s" '.
        ') as t '.
        'where d_time>0.0 and d_time<=%s ', $this->rank, $this->name, $this->threshold);

      $res = $db->get_row( $module_sql );

      $module_avg = $res['d_avg'];
      $module_stdev = $res['d_std'];
      $module_min = $res['d_min'];
      $module_max= $res['d_max'];

      $module_time_min = max(($module_avg - $this->standard_deviation_scale*$module_stdev),0);
      $module_time_max = $module_avg + $this->standard_deviation_scale*$module_stdev;

      $sql .= sprintf(
        'sum(if(duration is null, 0, '.
        'if(cast(trim("}" from substring_index(duration,":",-1)) as decimal(10,3))<%s,1,0))) as total_module_sub, ',$module_time_min);

      $sql .= sprintf(
        'sum(if(duration is null, 0, '.
        'if(cast(trim("}" from substring_index(duration,":",-1)) as decimal(10,3)) between %s and %s,1,0))) as total_module_par, ',$module_time_min,$module_time_max);

      $sql .= sprintf(
        'sum(if(duration is null, 0, '.
        'if(cast(trim("}" from substring_index(duration,":",-1)) as decimal(10,3))>%s,1,0))) as total_module_sup, ',$module_time_max);

      $this->page_explanation[]=sprintf('module time [min,max] : [%s,%s] ', util::time_to_label($module_min), util::time_to_label($module_max));
      $this->page_explanation[]=sprintf('module time [avg,stdev] : [%s,%s] ', util::time_to_label($module_avg), util::time_to_label($module_stdev));
      $this->page_explanation[]=sprintf('module time threshold : %s min', $this->threshold);
      $this->page_explanation[]=sprintf('subpar module time: time < %s (mean - %s x SD) min', $module_time_min, $this->standard_deviation_scale);
      $this->page_explanation[]=sprintf('par module time: %s <= time <= %d min', $module_time_min, $module_time_max);
      $this->page_explanation[]=sprintf('above par module time: time > %s (mean + %s x SD) min', $module_time_max, $this->standard_deviation_scale);

      $lag_sql = sprintf(
        'select avg(d_time) as d_avg, stddev(d_time) as d_std, min(d_time) as d_min, max(d_time) as d_max '.
        'from ( '.
        '  select cast(substring_index(substring_index(duration,",",1),":",-1) as decimal(10,3)) - '.
        '          cast(trim("}" from substring_index(duration,":",-1)) as decimal(10,3)) as d_time '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and duration is not null '.
        '  and s.name="%s" '.
        ') as t '.
        'where d_time>0.0 and d_time<=%s ', $this->rank, $this->name, $this->threshold);

      $res = $db->get_row( $lag_sql );

      $lag_avg = $res['d_avg'];
      $lag_stdev = $res['d_std'];
      $lag_min = $res['d_min'];
      $lag_max= $res['d_max'];

      $lag_time_min = max(($lag_avg - $this->standard_deviation_scale*$lag_stdev),0);
      $lag_time_max = $lag_avg + $this->standard_deviation_scale*$lag_stdev;

      $sql .= sprintf(
        'sum(if(duration is null, 0, '.
        'if(abs(cast(substring_index(substring_index(duration,",",1),":",-1) as decimal(10,3)) - '.
        '       cast(trim("}" from substring_index(duration,":",-1)) as decimal(10,3)))<%s,1,0))) as total_lag_sub, ',$lag_time_min);

      $sql .= sprintf(
        'sum(if(duration is null, 0, '.
        'if(abs(cast(substring_index(substring_index(duration,",",1),":",-1) as decimal(10,3)) - '.
        'cast(trim("}" from substring_index(duration,":",-1)) as decimal(10,3))) between %s and %s,1,0))) as total_lag_par, ',$lag_time_min,$lag_time_max);

      $sql .= sprintf(
        'sum(if(duration is null, 0, '.
        'if(abs(cast(substring_index(substring_index(duration,",",1),":",-1) as decimal(10,3)) - '.
        'cast(trim("}" from substring_index(duration,":",-1)) as decimal(10,3)))>%s,1,0))) as total_lag_sup, ',$lag_time_max);

      $this->page_explanation[]=sprintf('lag time [min,max] : [%s,%s] ', util::time_to_label($lag_min), util::time_to_label($lag_max));
      $this->page_explanation[]=sprintf('lag time [avg,stdev] : [%s,%s] ', util::time_to_label($lag_avg), util::time_to_label($lag_stdev));
      $this->page_explanation[]=sprintf('lag time threshold : %s min', $this->threshold);
      $this->page_explanation[]=sprintf('subpar lag time: time < %s (mean - %s x SD) min', $lag_time_min, $this->standard_deviation_scale);
      $this->page_explanation[]=sprintf('par lag time: %s <= time <= %d min', $lag_time_min, $lag_time_max);
      $this->page_explanation[]=sprintf('above par lag time: time > %s (mean + %s x SD) min', $lag_time_max, $this->standard_deviation_scale);
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
  }

  private $statistic;

  private $standard_deviation_scale;

  private $threshold;

  private $has_module;

  private $module_keys;
}
