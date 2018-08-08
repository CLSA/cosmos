<?php
require_once 'qnaire_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$disease_symptomns = new qnaire_generator(
  'disease_symptomns', $rank, $begin_date, $end_date);

$disease_symptomns->set_statistic($stat);
$disease_symptomns->set_standard_deviation_scale(2);
$disease_symptomns->set_page_stage('DISEASE SYMPTOMS');
$disease_symptomns->build_table_data();
echo $disease_symptomns->build_table_html();
