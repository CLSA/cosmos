<?php
require_once 'table_generator.class.php';

class qnaire_generator extends table_generator
{
  public function __construct($_stage, $_rank, $_begin_date = null, $_end_date = null)
  {
    parent::__construct($_stage, $_rank, $_begin_date, $_end_date);

    $this->indicator_keys = array('total_complete_sub','total_complete_par');
    $this->statistic = 'mean';            // default
  }

  public function set_statistic($_stat)
  {
    $this->statistic = $_stat;
  }

  protected function build_data()
  {
    global $db;

    $complete_perc=0;
    if('mode' == $this->statistic)
    {
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
      $complete_perc = intval($res['f_perc']);
    }
    else
    {
      $avg=0;
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
      $complete_perc = intval($res['favg']);
    }

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(cast(trim("}" from substring_index(qcdata,":",-1)) as signed)<%d,1,0))) as total_complete_sub, ',$complete_perc);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(cast( trim("}" from substring_index(qcdata,":",-1)) as signed)>=%d,1,0))) as total_complete_par, ',$complete_perc);

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
    $this->page_explanation[]=sprintf('percent complete sub: < %d%% (%s, no answer or other)', $complete_perc, $this->statistic);
    if(100==$complete_perc)
      $this->page_explanation[]=sprintf('percent complete par: = 100%% (%s, all answered no other)', $this->statistic);
    else  
      $this->page_explanation[]=sprintf('percent complete par: %d <= %% <= 100 (%s, all answered no other)', $complete_perc, $this->statistic);
  }

  private $statistic;
}
