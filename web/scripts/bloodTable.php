<?php
require_once 'qnaire_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$blood = new qnaire_generator(
  'blood', $rank, $begin_date, $end_date);

$blood->set_statistic($stat);
$blood->set_standard_deviation_scale(2);
$blood->set_page_stage('BLOOD');
$blood->build_table_data();
echo $blood->build_table_html();
