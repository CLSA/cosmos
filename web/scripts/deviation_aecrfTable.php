<?php
require_once 'qnaire_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$deviation_aecrf = new qnaire_generator(
  'deviation_aecrf', $rank, $begin_date, $end_date);
$deviation_aecrf->set_statistic($stat);
$deviation_aecrf->set_module_refusal(true);
$deviation_aecrf->set_page_stage('DEVIATION AE CRF');
$deviation_aecrf->build_table_data();
echo $deviation_aecrf->build_table_html();
