<?php
require_once 'qnaire_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$urine = new qnaire_generator(
  'urine', $rank, $begin_date, $end_date);

$urine->set_statistic($stat);
$urine->set_standard_deviation_scale(2);
$urine->set_page_stage('URINE');
$urine->build_table_data();
echo $urine->build_table_html();
