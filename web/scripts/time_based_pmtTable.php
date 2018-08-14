<?php
require_once 'qnaire_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$time_based_pmt = new qnaire_generator(
  'time_based_pmt', $rank, $begin_date, $end_date);

$time_based_pmt->set_statistic($stat);
$time_based_pmt->set_page_stage('TIME BASED PMT');
$time_based_pmt->build_table_data();
echo $time_based_pmt->build_table_html();
