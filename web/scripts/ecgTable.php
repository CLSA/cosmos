<?php
require_once 'ecg_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$ecg = new ecg_generator('ecg', $rank, $begin_date, $end_date);
$ecg->set_statistic($stat);
$ecg->set_standard_deviation_scale(2);
$ecg->build_table_data();
echo $ecg->build_table_html();
