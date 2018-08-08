<?php
require_once 'table_generator.class.php';

class standing_balance_generator extends table_generator
{
  public function __construct($_stage, $_rank, $_begin_date = null, $_end_date = null)
  {
    parent::__construct($_stage, $_rank, $_begin_date, $_end_date);

    $this->indicator_keys=array(
      'total_best_time_sub','total_best_time_par','total_best_time_sup');

    $this->standard_deviation_scale = 1;
    $this->page_stage ='STANDING BALANCE';
  }

  public function set_standard_deviation_scale($_stdev)
  {
    $this->standard_deviation_scale = $_stdev;
  }

  protected function build_data()
  {
    global $db;

    // build the main query

    $sql = sprintf(
      'select avg(s_time) as t_avg from '.
      '('.
      '  ( '.
      '    select '.
      '      cast( substring_index( '.
      '        substring_index( '.
      '          qcdata, ",", 2 ), ":", -1) as decimal) as s_time '.
      '    from interview i '.
      '    join stage s on i.id=s.interview_id '.
      '    where rank=%d '.
      '    and qcdata is not null '.
      '    and s.name="%s" '.
      '  ) '.
      '  union all '.
      '  ( '.
      '    select '.
      '      cast( trim( "}" from '.
      '        substring_index( '.
      '          substring_index( '.
      '            qcdata, ",", -1 ), ":", -1 ) ) as decimal) as s_time '.
      '    from interview i '.
      '    join stage s on i.id=s.interview_id '.
      '    where rank=%d '.
      '    and qcdata is not null '.
      '    and s.name="%s" '.
      '  ) '.
      ') as t '.
      'where s_time>0', $this->rank, $this->name, $this->rank, $this->name);

    $res = $db->get_one( $sql );
    if( false === $res )
    {
      echo sprintf('failed to get average standing balance time: %s', $db->get_last_error() );
      echo $sql;
      die();
    }

    $avg = $res;

    $sql = sprintf(
      'select stddev(s_time) as t_std from '.
      '('.
      '  ( '.
      '    select '.
      '      cast( substring_index( '.
      '        substring_index( '.
      '          qcdata, ",", 2 ), ":", -1) as decimal) as s_time '.
      '    from interview i '.
      '    join stage s on i.id=s.interview_id '.
      '    where rank=%d '.
      '    and qcdata is not null '.
      '    and s.name="%s" '.
      '  ) '.
      '  union all '.
      '  ( '.
      '    select '.
      '      cast( trim( "}" from '.
      '        substring_index( '.
      '          substring_index( '.
      '            qcdata, ",", -1 ), ":", -1 ) ) as decimal) as s_time '.
      '    from interview i '.
      '    join stage s on i.id=s.interview_id '.
      '    where rank=%d '.
      '    and qcdata is not null '.
      '    and s.name="%s" '.
      '  ) '.
      ') as t '.
      'where s_time>0', $this->rank, $this->name, $this->rank, $this->name);

    $res = $db->get_one( $sql );
    if( false === $res )
    {
      echo sprintf('failed to get stddev standing balance time: %s', $db->get_last_error() );
      echo $sql;
      die();
    }
    $std = $res;
    $time_min = max(round($avg - $this->standard_deviation_scale*$std,3),0);
    $time_max = round($avg + $this->standard_deviation_scale*$std,3);

    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(cast(substring_index(substring_index(qcdata,",",1),":",-1) as decimal)<%s,1,0))) as total_best_time_sub, ',$time_min);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(cast(substring_index(substring_index(qcdata,",",1),":",-1) as decimal) between %s and %s,1,0))) as total_best_time_par, ',$time_min,$time_max);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(cast(substring_index(substring_index(qcdata,",",1),":",-1) as decimal)>%s,1,0))) as total_best_time_sup, ',$time_max);

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
    $this->page_explanation[]=sprintf('best time sub: time < %s sec (mean - %s x SD)',$time_min, $this->standard_deviation_scale);
    $this->page_explanation[]=sprintf('best time par: %s <= time <= %s sec',$time_min,$time_max);
    $this->page_explanation[]=sprintf('best time sup: time > %s sec (mean + %s x SD)',$time_max, $this->standard_deviation_scale);
  }

  private $standard_deviation_scale;

}
