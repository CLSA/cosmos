<?php
require_once 'single_file_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$cdtt = new single_file_generator(
  'cdtt', $rank, $begin_date, $end_date);
$cdtt->set_file_scale(1.0);
$cdtt->set_statistic($stat);
$cdtt->set_standard_deviation_scale(1);
$cdtt->build_table_data();
echo $cdtt->build_table_html();
