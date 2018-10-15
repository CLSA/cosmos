<?php
require_once 'table_generator.class.php';

class timed_move_generator extends table_generator
{
  public function __construct($_stage, $_rank, $_begin_date = null, $_end_date = null)
  {
    parent::__construct($_stage, $_rank, $_begin_date, $_end_date);

    $this->indicator_keys = array('total_test_time_sub','total_test_time_par','total_test_time_sup');
    $this->percent_keys = array('total_incongruency','total_skip','total_unexplained_missing','total_contraindicated');
    $this->standard_deviation_scale= 2;  // default
    $this->congruency_threshold = 10; // default
  }

  public function set_congruency_threshold($_thresh)
  {
    if( 0 < $_thresh )
    {
      $this->congruency_threshold = $_thresh;
    }
  }

  protected function build_data()
  {
    global $db;

    // build the main query
    $sql = sprintf(
        'select avg(t_time) as t_avg, stddev(t_time) as t_std '.
        'from ( '.
        '  select cast(substring_index(substring_index(qcdata,",",1),":",-1) as decimal(10,3)) as t_time '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and qcdata is not null '.
        '  and s.name="%s" '.
        ') as t', $this->rank, $this->name);

    $res = $db->get_row( $sql );
    if( false === $res )
    {
      echo sprintf('failed to get test time data: %s', $db->get_last_error() );
      echo $sql;
      die();
    }
    $avg = $res['t_avg'];
    $stdev = $res['t_std'];

    $test_time_min = intval(round($avg - $this->standard_deviation_scale*$stdev));
    $test_time_max = intval(round($avg + $this->standard_deviation_scale*$stdev));

    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(cast(substring_index(substring_index(qcdata,",", 1),":",-1) as decimal)<%d,1,0))) as total_test_time_sub, ',$test_time_min);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(cast(substring_index(substring_index(qcdata,",",1),":",-1) as decimal) between %d and %d,1,0))) as total_test_time_par, ',$test_time_min,$test_time_max);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(cast(substring_index(substring_index(qcdata,",", 1),":",-1) as decimal)>%d,1,0))) as total_test_time_sup, ',$test_time_max);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
        'if(cast(trim("}" from substring_index(qcdata,":",-1)) as decimal)>%s,1,0))) as total_incongruency, ', $this->congruency_threshold);

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
    $this->page_explanation[]=sprintf('subpar test time: time < %s sec (mean - %s x SD)',$test_time_min, $this->standard_deviation_scale);
    $this->page_explanation[]=sprintf('par test time: %s <= time <= %s sec',$test_time_min,$test_time_max);
    $this->page_explanation[]=sprintf('above par test time: time > %s sec (mean + %s x SD)',$test_time_max, $this->standard_deviation_scale);
    $this->page_explanation[]=sprintf(
      'congruency threshold = abs( tug - (1.5 x four metre walk + chair rise) ) > %s sec',$this->congruency_threshold);
  }

  public function build_table_data()
  {
    parent::build_table_data();

    $this->indicator_keys[] = 'total_incongruency';
  }

  private $congruency_threshold;
}
