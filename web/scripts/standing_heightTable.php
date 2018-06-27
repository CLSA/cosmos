<?php
require_once 'table_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

class standing_height_generator extends table_generator
{
  protected function build_data()
  {
    global $db;

    $standing_height_dev_min = 0.05;
    $standing_height_dev_max = 1.0;

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(substring_index(substring_index(qcdata,",",1),":",-1)<%s,1,0))) as total_standing_height_sub, ', $standing_height_dev_min);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(substring_index(substring_index(qcdata,",",1),":",-1) between %s and %s,1,0))) as total_standing_height_par, ',$standing_height_dev_min,$standing_height_dev_max);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(substring_index(substring_index(qcdata,",",1),":",-1)>%s,1,0))) as total_standing_height_sup, ',$standing_height_dev_max);

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
    $this->page_explanation[]='Height deviation = standard deviation of repeated scale measurements';
    $this->page_explanation[]=sprintf('standing height deviation sub: size < %s cm (scale resolution)',$standing_height_dev_min);
    $this->page_explanation[]=sprintf('standing height deviation par: %s <= size <= %s cm',$standing_height_dev_min,$standing_height_dev_max);
    $this->page_explanation[]=sprintf('standing height deviation sup: size > %s cm',$standing_height_dev_max);
    $this->page_explanation[]='Trial deviation signalled when more or less than 2 measurements made';
  }
}

$standing_height = new standing_height_generator('standing_height', $rank, $begin_date, $end_date);

$qc_keys=array('total_standing_height_sub','total_standing_height_par','total_standing_height_sup');
$standing_height->set_indicator_keys($qc_keys);
$percent_keys = array('total_trial_deviation','total_skip','total_unexplained_missing','total_contraindicated');
$standing_height->set_percent_keys($percent_keys);

$standing_height->build_table_data();
$qc_keys[] = 'total_trial_deviation';
$standing_height->set_indicator_keys($qc_keys);
$standing_height->set_page_stage('STANDING HEIGHT');

echo $standing_height->build_table_html();
