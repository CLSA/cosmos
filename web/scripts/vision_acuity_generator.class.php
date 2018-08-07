<?php
require_once 'table_generator.class.php';

class vision_acuity_generator extends table_generator
{
  public function __construct($_stage, $_rank, $_begin_date = null, $_end_date = null)
  {
    parent::__construct($_stage, $_rank, $_begin_date, $_end_date);

    $this->indicator_keys=array(
      'total_left_sub','total_left_par','total_right_sub','total_right_par');

    $this->page_stage ='VISION ACUITY';
  }

  protected function build_data()
  {
    global $db;

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    $sql .= 'sum(if(qcdata is null, 0, if(substring_index(substring_index(qcdata,",",1),":",-1) between 0 and 1,1,0))) as total_left_sub, ';
    $sql .= 'sum(if(qcdata is null, 0, if(substring_index(substring_index(qcdata,",",1),":",-1)=2,1,0))) as total_left_par, ';
    $sql .= 'sum(if(qcdata is null, 0, if(substring_index(substring_index(qcdata,",",-1),":",-1) between 0 and 1,1,0))) as total_right_sub, ';
    $sql .= 'sum(if(qcdata is null, 0, if(substring_index(substring_index(qcdata,",",-1),":",-1)=2,1,0))) as total_right_par, ';

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
    $this->page_explanation[]='measure count sub:  0 - 1 measures</li>';
    $this->page_explanation[]='measure count par:  2 measures';
  }
}
