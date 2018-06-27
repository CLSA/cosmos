<?php
require_once 'table_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

class tonometer_generator extends table_generator
{
  protected function build_data()
  {
    global $db;

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    $sql .=
      'sum(if(qcdata is null, 0, '.
      'if(substring_index(substring_index(qcdata,",",2),":",-1)<2.5,1,0))) as total_quality_low_left, ';

    $sql .=
      'sum(if(qcdata is null, 0, '.
      'if(substring_index(substring_index(qcdata,",",2),":",-1) between 2.5 and 7.5,1,0))) as total_quality_medium_left, ';

    $sql .=
      'sum(if(qcdata is null, 0, '.
      'if(substring_index(substring_index(qcdata,",",2),":",-1)>7.5,1,0))) as total_quality_high_left, ';

    $sql .=
      'sum(if(qcdata is null, 0, '.
      'if(substring_index(substring_index(qcdata,",",6),":",-1)<2.5,1,0))) as total_quality_low_right, ';

    $sql .=
      'sum(if(qcdata is null, 0, '.
      'if(substring_index(substring_index(qcdata,",",6),":",-1) between 2.5 and 7.5,1,0))) as total_quality_medium_right, ';

    $sql .=
      'sum(if(qcdata is null, 0, '.
      'if(substring_index(substring_index(qcdata,",",6),":",-1)>7.5,1,0))) as total_quality_high_right, ';

    $sql .=
      'sum(if(qcdata is null, 0, '.
      'if(substring_index(substring_index(qcdata,",",3),":",-1)=400 AND '.
      '   substring_index(substring_index(qcdata,",",4),":",-1)=400,1,0))) as total_curve_left, ';

    $sql .=
      'sum(if(qcdata is null, 0, '.
      'if(substring_index(substring_index(qcdata,",",7),":",-1)=400 AND '.
      '   trim("}" from substring_index(substring_index(qcdata,",",8),":",-1))=400,1,0))) as total_curve_right, ';

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
    $this->page_explanation[]='tonometer quality index 0 - 10';
    $this->page_explanation[]='quality low: q < 2.5';
    $this->page_explanation[]='quality medium: 2.5 <  q < 7.5';
    $this->page_explanation[]='quality high: q > 7.5';
    $this->page_explanation[]='curve availability if both pressure and applanation data are present';
  }
}

$tonometer = new tonometer_generator('tonometer', $rank, $begin_date, $end_date);

$qc_keys=array(
  'total_quality_low_left','total_quality_medium_left','total_quality_high_left',
  'total_quality_low_right','total_quality_medium_right','total_quality_high_right');
$percent_keys = array(
  'total_curve_left','total_curve_right','total_skip','total_unexplained_missing','total_contraindicated');

$tonometer->set_indicator_keys($qc_keys);
$tonometer->set_percent_keys($percent_keys);

$tonometer->build_table_data();

$qc_keys[]='total_curve_left';
$qc_keys[]='total_curve_right';
$tonometer->set_indicator_keys($qc_keys);

$tonometer->set_page_stage('TONOMETER');

echo $tonometer->build_table_html();
