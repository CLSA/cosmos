<?php
require_once 'table_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

class weight_generator extends table_generator
{
  protected function build_data()
  {
    global $db;

    $weight_dev_min = 0.05;
    $weight_dev_max = 1.0;

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(substring_index(substring_index(qcdata,",",1),":",-1)<%s,1,0))) as total_weight_sub, ', $weight_dev_min);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(substring_index(substring_index(qcdata,",",1),":",-1) between %s and %s,1,0))) as total_weight_par, ',$weight_dev_min,$weight_dev_max);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(substring_index(substring_index(qcdata,",",1),":",-1)>%s,1,0))) as total_weight_sup, ',$weight_dev_max);

    $sql .=
      'sum(if(qcdata is null, 0, '.
      'if(trim("{" from substring_index(substring_index(qcdata,",",2),":",-1))!=2,1,0))) as total_trial_deviation, ';

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
    $this->page_explanation[]='Weight deviation = standard deviation of repeated scale measurements';
    $this->page_explanation[]=sprintf('weight deviation sub: size < %s kg (scale resolution)',$weight_dev_min);
    $this->page_explanation[]=sprintf('weight deviation par: %s <= size <= %s kg',$weight_dev_min,$weight_dev_max);
    $this->page_explanation[]=sprintf('weight deviation sup: size > %s kg',$weight_dev_max);
    $this->page_explanation[]='Trial deviation signalled when more or less than 2 measurements made';
  }
}

$weight = new weight_generator('weight', $rank, $begin_date, $end_date);

$qc_keys=array('total_weight_sub','total_weight_par','total_weight_sup');
$weight->set_indicator_keys($qc_keys);
$percent_keys = array('total_trial_deviation','total_skip','total_unexplained_missing','total_contraindicated');
$weight->set_percent_keys($percent_keys);

$weight->build_table_data();
$qc_keys[] = 'total_trial_deviation';
$weight->set_indicator_keys($qc_keys);
$weight->set_page_stage('WEIGHT');

echo $weight->build_table_html();
