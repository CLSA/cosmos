<?php
require_once 'qnaire_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$quality_assurance_inhome = new qnaire_generator(
  'quality_assurance_inhome', $rank, $begin_date, $end_date);
$quality_assurance_inhome->set_statistic($stat);
$quality_assurance_inhome->set_page_stage('QUALITY ASSURANCE IH');
$quality_assurance_inhome->build_table_data();
echo $quality_assurance_inhome->build_table_html();
