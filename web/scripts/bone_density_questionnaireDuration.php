<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$bone_density_questionnaire = new duration_generator('bone_density_questionnaire', $rank, $begin_date, $end_date);

$smin = htmlspecialchars($_POST['stage-dur-min']);
$smax = htmlspecialchars($_POST['stage-dur-max']);

if(
  is_numeric($smin) &&
  is_numeric($smax)
  $smax>$smin)
{
  $bone_density_questionnaire->set_par_time_range(array($smin,$smax,$mmin,$mmax));
}

$bone_density_questionnaire->set_threshold(10);
$bone_density_questionnaire->set_standard_deviation_scale(1);
$bone_density_questionnaire->build_table_data();
echo $bone_density_questionnaire->build_table_html();
