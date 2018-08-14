<?php
require_once 'qnaire_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$social_network = new qnaire_generator(
  'social_network', $rank, $begin_date, $end_date);

$social_network->set_statistic($stat);
$social_network->set_page_stage('SOCIAL NETWORK');
$social_network->build_table_data();
echo $social_network->build_table_html();
