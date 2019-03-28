<?php
require_once 'qnaire_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$stroop_fas = new qnaire_generator(
  'stroop_fas', $rank, $begin_date, $end_date);
$stroop_fas->set_statistic($stat);
$stroop_fas->set_module_refusal(true);
$stroop_fas->build_table_data();
echo $stroop_fas->build_table_html();
