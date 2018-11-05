<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$grip_strength = new duration_generator('grip_strength', $rank, $begin_date, $end_date);

$smin = htmlspecialchars($_POST['stage-dur-min']);
$smax = htmlspecialchars($_POST['stage-dur-max']);

if(
  is_numeric($smin) &&
  is_numeric($smax)
  $smax>$smin)
{
  $grip_strength->set_par_time_range(array($smin,$smax,$mmin,$mmax));
}

$grip_strength->set_threshold(30);
$grip_strength->set_standard_deviation_scale(2);
$grip_strength->build_table_data();
echo $grip_strength->build_table_html();
