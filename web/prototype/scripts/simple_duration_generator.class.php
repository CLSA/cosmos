<?php

require_once 'table_generator.class.php';

class simple_duration_generator extends table_generator
{
  public function __construct($_stage, $_rank, $_begin_date = null, $_end_date = null)
  {
    global $db;
    parent::__construct($_stage, $_rank, $_begin_date, $_end_date);

    $this->indicator_keys = array('total_time_sub','total_time_par','total_time_sup');
    $this->threshold = 60; // default 60 minutes
    $this->par_time_range = null;
    $this->standard_deviation_scale = 1;
    $this->statistics = null;
  }

  public function get_statistics()
  {
    return $this->statistics;
  }

  public function set_threshold($_thresh)
  {
    $this->threshold = $_thresh;
  }

  public function set_par_time_range($_range)
  {
    $this->par_time_range = $_range;
  }

  protected function build_data()
  {
    global $db;

    // stage statistics based on the entire rank to date
    $sql = sprintf(
        'select avg(d_time) as d_avg, stddev(d_time) as d_std, min(d_time) as d_min, max(d_time) as d_max '.
        'from ( '.
        '  select cast(substring_index(substring_index(s.duration,",",1),":",-1) as decimal(10,3)) as d_time '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and s.duration is not null '.
        '  and s.name="%s" '.
        ') as t '.
        'where d_time>0.0 and d_time<=%s ', $this->rank, $this->name, $this->threshold);

    $res = $db->get_row( $sql );

    $stage_avg = $res['d_avg'];
    $stage_stdev = $res['d_std'];
    $stage_min = $res['d_min'];
    $stage_max= $res['d_max'];

    if(null==$this->par_time_range)
    {
      $stage_time_min = max(($stage_avg - $this->standard_deviation_scale*$stage_stdev),0);
      $stage_time_max = $stage_avg + $this->standard_deviation_scale*$stage_stdev;
    }
    else
    {
      $stage_time_min = $this->par_time_range[0]/60.0;
      $stage_time_max = $this->par_time_range[1]/60.0;
    }

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    $sql .= sprintf(
      'sum(if(s.duration is null, 0, '.
      'if(cast(substring_index(substring_index(s.duration,",",1),":",-1) as decimal(10,3))<%s,1,0))) as total_time_sub, ',$stage_time_min);

    $sql .= sprintf(
      'sum(if(s.duration is null, 0, '.
      'if(cast(substring_index(substring_index(s.duration,",",1),":",-1) as decimal(10,3)) between %s and %s,1,0))) as total_time_par, ',$stage_time_min,$stage_time_max);

    $sql .= sprintf(
      'sum(if(s.duration is null, 0, '.
      'if(cast(substring_index(substring_index(s.duration,",",1),":",-1) as decimal(10,3))>%s,1,0))) as total_time_sup, ',$stage_time_max);

    $sql .=
      'sum(if(s.duration is null, 0, '.
      'cast(substring_index(substring_index(s.duration,",",1),":",-1) as decimal(10,3)))) as total_time, ';

    $sql .=
      'sum(if(s.duration is null, 0, 1)) as total_count, ';

    $sql .= $this->get_main_query();

    $res = $db->get_all( $sql );
    if(false===$res || !is_array($res))
    {
      echo sprintf('error: failed query: %s', $db->get_last_error());
      echo $sql;
      die();
    }
    $this->data = $res;

    $this->statistics =
      array('avg'=>$stage_avg,'std'=>$stage_stdev,'min'=>$stage_min,'max'=>$stage_max);
  }

  private $threshold;

  private $par_time_range;

  private $statistics;
}
