<?php
require_once 'qnaire_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$general_health = new qnaire_generator(
  'general_health', $rank, $begin_date, $end_date);
$general_health->set_statistic($stat);
$general_health->set_module_refusal(true);
$general_health->build_table_data();
echo $general_health->build_table_html();
