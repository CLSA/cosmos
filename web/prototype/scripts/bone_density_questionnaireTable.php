<?php
require_once 'qnaire_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$bone_density_questionnaire = new qnaire_generator(
  'bone_density_questionnaire', $rank, $begin_date, $end_date);
$bone_density_questionnaire->set_statistic($stat);
$bone_density_questionnaire->build_table_data();
echo $bone_density_questionnaire->build_table_html();
