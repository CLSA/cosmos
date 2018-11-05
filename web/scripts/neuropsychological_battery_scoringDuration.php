<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$neuropsychological_battery_scoring = new duration_generator('neuropsychological_battery_scoring', $rank, $begin_date, $end_date);

$smin = htmlspecialchars($_POST['stage-dur-min']);
$smax = htmlspecialchars($_POST['stage-dur-max']);

if(
  is_numeric($smin) &&
  is_numeric($smax)
  $smax>$smin)
{
  $neuropsychological_battery_scoring->set_par_time_range(array($smin,$smax,$mmin,$mmax));
}

$neuropsychological_battery_scoring->set_threshold(30);
$neuropsychological_battery_scoring->set_standard_deviation_scale(1);
$neuropsychological_battery_scoring->build_table_data();
echo $neuropsychological_battery_scoring->build_table_html();
