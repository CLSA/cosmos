<?php
require_once 'single_file_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$retinal_scan_right = new single_file_generator(
  'retinal_scan_right', $rank, $begin_date, $end_date);
$retinal_scan_right->set_file_scale(1024.0);
$retinal_scan_right->set_statistic($stat);
$retinal_scan_right->set_standard_deviation_scale(2);
$retinal_scan_right->build_table_data();
echo $retinal_scan_right->build_table_html();
