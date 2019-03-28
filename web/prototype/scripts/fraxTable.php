<?php
require_once 'single_file_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$frax = new single_file_generator(
  'frax', $rank, $begin_date, $end_date);
$frax->set_file_scale(1.0);
$frax->set_statistic($stat);
$frax->set_standard_deviation_scale(2);
$frax->set_page_stage('DEXA FRAX');
$frax->build_table_data();
echo $frax->build_table_html();
