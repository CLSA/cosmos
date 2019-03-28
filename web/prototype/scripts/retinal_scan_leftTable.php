<?php
require_once 'single_file_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$retinal_scan_left = new single_file_generator(
  'retinal_scan_left', $rank, $begin_date, $end_date);
$retinal_scan_left->set_file_scale(1024.0);
$retinal_scan_left->set_statistic($stat);
$retinal_scan_left->set_standard_deviation_scale(2);
$retinal_scan_left->build_table_data();
echo $retinal_scan_left->build_table_html();
