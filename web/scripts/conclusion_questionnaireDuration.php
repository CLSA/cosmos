<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$conclusion_questionnaire = new duration_generator('conclusion_questionnaire', $rank, $begin_date, $end_date);

$smin = htmlspecialchars($_POST['stage-dur-min']);
$smax = htmlspecialchars($_POST['stage-dur-max']);

if(
  is_numeric($smin) &&
  is_numeric($smax)
  $smax>$smin)
{
  $conclusion_questionnaire->set_par_time_range(array($smin,$smax,$mmin,$mmax));
}

$conclusion_questionnaire->set_threshold(10);
$conclusion_questionnaire->set_standard_deviation_scale(1);
$conclusion_questionnaire->build_table_data();
echo $conclusion_questionnaire->build_table_html();
