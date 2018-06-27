<?php
require_once 'table_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

class spirometry_generator extends table_generator
{
  public set_indicator_keys( $_keys )
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

$spirometry = new spirometry_generator('spirometry', $rank, $begin_date, $end_date);

$spirometry->build_table_data();

$spirometry->set_page_stage('SPIROMETRY');

echo $spirometry->build_table_html();
