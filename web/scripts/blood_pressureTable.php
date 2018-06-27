<?php
require_once 'table_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

class blood_pressure_generator extends table_generator
{
  protected function build_data()
  {
    global $db;

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    $sql .= 'sum(if(qcdata is null, 0, if(trim("}" from substring_index(qcdata,":",-1))<5,1,0))) as total_trial_sub, ';
    $sql .= 'sum(if(qcdata is null, 0, if(trim("}" from substring_index(qcdata,":",-1))=5,1,0))) as total_trial_par, ';
    $sql .= 'sum(if(qcdata is null, 0, if(trim("}" from substring_index(qcdata,":",-1))>5,1,0))) as total_trial_sup, ';

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
    $this->page_explanation[]='trial sub: < 5 trials';
    $this->page_explanation[]='trial par: = 5 trials';
    $this->page_explanation[]='trial sup: > 5 trials';
  }
}

$blood_pressure = new blood_pressure_generator('blood_pressure', $rank, $begin_date, $end_date);

$qc_keys=array('total_trial_sub','total_trial_par','total_trial_sup');
$blood_pressure->set_indicator_keys($qc_keys);

$blood_pressure->build_table_data();

$blood_pressure->set_page_stage('BLOOD PRESSURE');

echo $blood_pressure->build_table_html();
