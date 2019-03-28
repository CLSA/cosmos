<?php
require_once 'qnaire_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$disease_symptoms = new qnaire_generator(
  'disease_symptoms', $rank, $begin_date, $end_date);
$disease_symptoms->set_statistic($stat);
$disease_symptoms->set_module_refusal(true);
$disease_symptoms->build_table_data();
echo $disease_symptoms->build_table_html();
