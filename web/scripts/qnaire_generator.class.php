<?php
require_once 'table_generator.class.php';

class qnaire_generator extends table_generator
{
  public function __construct($_stage, $_rank, $_begin_date = null, $_end_date = null)
  {
    parent::__construct($_stage, $_rank, $_begin_date, $_end_date);

    $this->indicator_keys = array('total_complete_sub','total_complete_par');
    $this->statistic = 'mean';            // default
    $this->module_refusal = false;
    $this->module_threshold = 90;
    $this->standard_deviation_scale = 1.0;
  }

  public function set_module_refusal($_refuse)
  {
    $this->module_refusal = $_refuse;
  }

  public function set_module_threshold($_t)
  {
    $this->module_threshold = $_t;
  }

  protected function build_data()
  {
    global $db;

    $complete_perc_min=0;
    $complete_perc_max=100;
    if('mode' == $this->statistic)
    {
      $mode=0;
      $sql = sprintf(
        'select f_perc, count(f_perc) as freq '.
        'from ( '.
        '  select cast('.
        '    substring_index( '.
        '      substring_index(qcdata, ",", 1),":",-1) as signed)) as f_perc '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and qcdata is not null '.
        '  and s.name="%s" '.
        ') as t '.
        'group by f_perc '.
        'order by freq desc limit 1', $this->rank, $this->name);

      $res = $db->get_row( $sql );
      if( false === $res )
      {
        echo sprintf('failed to get qnaire percent missing data: %s', $db->get_last_error() );
        echo $sql;
        die();
      }
      $mode = $res['f_perc'];

      $sql = sprintf(
        'select min(f_perc) as min_perc, max(f_perc) as max_perc '.
        'from ( '.
        '  select '.
        '    substring_index(substring_index(qcdata,",",1),":",-1) as decimal(6,3)) as f_perc '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and qcdata is not null '.
        '  and s.name="%s" '.
        ') as t', $this->rank, $this->name);

      $res = $db->get_row( $sql );
      if( false === $res )
      {
        echo sprintf('failed to get file size data: %s', $db->get_last_error() );
        echo $sql;
        die();
      }
      $min = $res['min_perc'];
      $max = $res['max_perc'];
      $complete_perc_min = max(intval(($min + 0.5*($mode-$min))),0);
      $complete_perc_max = min(intval(($mode + 0.5*($max-$mode))),100);
    }
    else
    {
      $avg=0;
      $sql = sprintf(
        'select avg(f_perc) as favg, stddev(f_perc) as fstd '.
        'from ( '.
        '  select cast( '.
        '    substring_index( '.
        '      substring_index(qcdata,",",1),":",-1) as decimal(6,3)) as f_perc '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and qcdata is not null '.
        '  and s.name="%s" '.
        ') as t ', $this->rank, $this->name);

      $res = $db->get_row( $sql );
      if( false === $res )
      {
        echo sprintf('failed to get qnaire percent missing data: %s', $db->get_last_error() );
        echo $sql;
        die();
      }
      $avg = $res['favg'];
      $stdev = $res['fstd'];

      $complete_perc_min = max(intval(($avg - $this->standard_deviation_scale*$stdev)),0);
      $complete_perc_max = min(intval(($avg + $this->standard_deviation_scale*$stdev)),100);
    }

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(cast(substring_index(substring_index(qcdata,",",1),":",-1) as signed)<%d,1,0))) as total_complete_sub, ',$complete_perc_min);

    if($complete_perc_min==$complete_perc_max)
    {
      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(cast(substring_index(substring_index(qcdata,",",1),":",-1) as signed)=%d,1,0))) as total_complete_par, ',$complete_perc_min);
    }
    else
    {
      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(cast(substring_index(substring_index(qcdata,",",1),":",-1) as signed) between %d and %d,1,0))) as total_complete_par, ',
        $complete_perc_min,$complete_perc_max);
    }

    if(100>$complete_perc_max)
    {
      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(cast(substring_index(substring_index(qcdata,",",1),":",-1) as signed)>%d,1,0))) as total_complete_sup, ',$complete_perc_max);
      $this->indicator_keys[] = 'total_complete_sup';
    }
    $this->group_indicator_keys = array('complete'=>$this->indicator_keys);
   
    if($this->module_refusal)
    {
      // get the names of the modules
      $sql_module = sprintf(
        'select qcdata '.
        'from stage s '.
        'join interview i on i.id=s.interview_id '.
        'where name="%s" '.
        'and rank=%d '.
        'and qcdata is not null '.
        'limit 1', $this->name, $this->rank);
      $res = $db->get_one( $sql_module );
      if(false===$res) {
        var_dump($sql_module);
        die();
      }
      preg_match_all('/,(.*?):/',$res,$module_name_list);
      $module_name_list = array_pop($module_name_list);

      $this->group_indicator_keys['module'] = array();

      for( $i = 0; $i < count($module_name_list); $i++ )
      {
        $refusal_key = 'total_refusal_'.$module_name_list[$i];
        $this->indicator_keys[] = $refusal_key;
        $this->group_indicator_keys['module'][] = $refusal_key;

        $sql .= sprintf(
          'sum(if(qcdata is null, 0, '.
          'if(cast(substring_index(substring_index(qcdata,",",%d),":",-1) as decimal(6,3))>%s,1,0))) as total_refusal_%s, ',
          $i+2,
          $this->module_threshold,
          $module_name_list[$i]);
      }
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
    $this->page_explanation[] = sprintf('subpar percent: < %s%% (%s)', $complete_perc_min, $this->statistic);
    $this->page_explanation[] = sprintf('on par percent: [%s%% - %s%%] (%s)', $complete_perc_min, $complete_perc_max, $this->statistic);
    if(100>$complete_perc_max)
    {
      $this->page_explanation[] = sprintf('above par percent: > %s%% (%s)', $complete_perc_max, $this->statistic);
    }
    if($this->module_refusal)
    {
      $this->page_explanation[] = sprintf('module refusal threshold: > %s%%', $this->module_threshold);
    }
  }

  private $module_refusal;

  private $module_threshold;
}
