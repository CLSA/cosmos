<?php
require_once 'qnaire_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$neuropsychological_battery_scoring = new qnaire_generator(
  'neuropsychological_battery_scoring', $rank, $begin_date, $end_date);

$neuropsychological_battery_scoring->set_statistic($stat);
$neuropsychological_battery_scoring->set_page_stage('OSIPV');
$neuropsychological_battery_scoring->build_table_data();
echo $neuropsychological_battery_scoring->build_table_html();
