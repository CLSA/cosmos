<?php
require_once 'table_generator.class.php';

class trial_generator extends table_generator
{
  public function __construct($_stage, $_rank, $_begin_date = null, $_end_date = null)
  {
    parent::__construct($_stage, $_rank, $_begin_date, $_end_date);

    $this->indicator_keys = array('total_trial_sub','total_trial_par','total_trial_sup');
    $this->trial_target = 3;
  }

  public function set_trial_target( $_target )
  {
    if( 0 < $_target )
      $this->trial_target = $_target;
  }

  protected function build_data()
  {
    global $db;

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    $sql .= sprintf('sum(if(qcdata is null, 0, if(cast(trim("}" from substring_index(qcdata,":",-1)) as signed)<%s,1,0))) as total_trial_sub, ', $this->trial_target);
    $sql .= sprintf('sum(if(qcdata is null, 0, if(cast(trim("}" from substring_index(qcdata,":",-1)) as signed)=%s,1,0))) as total_trial_par, ', $this->trial_target);
    $sql .= sprintf('sum(if(qcdata is null, 0, if(cast(trim("}" from substring_index(qcdata,":",-1)) as signed)>%s,1,0))) as total_trial_sup, ', $this->trial_target);

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
    $this->page_explanation[]=sprintf('trial sub: < %s trials', $this->trial_target);
    $this->page_explanation[]=sprintf('trial par: = %s trials', $this->trial_target);
    $this->page_explanation[]=sprintf('trial sup: > %s trials', $this->trial_target);
  }

  protected $trial_target;
}
