<?php
require_once 'table_generator.class.php';

class spirometry_generator extends table_generator
{
  public function __construct($_stage, $_rank, $_begin_date = null, $_end_date = null)
  {
    parent::__construct($_stage, $_rank, $_begin_date, $_end_date);

    $this->page_stage = 'SPIROMETRY';
  }

  public function set_indicator_keys( $_keys )
  {
    // do nothing: the keys are auto generated
  }

  protected function build_data()
  {
    global $db;

    $grade_list=array(
      1=>array('A','B','C','C1','C2','D1','D2','F'),
      2=>array('A','B'));

    if( 2 == $this->rank )
      $grades = $grade_list[$this->rank];
    else
      $grades = $grade_list[1];

    $this->indicator_keys=array();

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    foreach($grades as $grade)
    {
      $sql .= sprintf('sum(case when strcmp(qcdata,"{grade:%s}")=0 then 1 else 0 end) as total_%s, ',$grade,$grade);
      $this->indicator_keys[] = sprintf('total_%s',$grade);
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

    $this->page_explanation[]=sprintf('Grades: %s',implode(', ',$grades));
  }
}
