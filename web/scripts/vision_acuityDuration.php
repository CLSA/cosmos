<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$vision_acuity = new duration_generator('vision_acuity', $rank, $begin_date, $end_date);

$smin = htmlspecialchars($_POST['stage-dur-min']);
$smax = htmlspecialchars($_POST['stage-dur-max']);

if(
  is_numeric($smin) &&
  is_numeric($smax)
  $smax>$smin)
{
  $vision_acuity->set_par_time_range(array($smin,$smax,$mmin,$mmax));
}

$vision_acuity->set_threshold(20);
$vision_acuity->set_standard_deviation_scale(1);
$vision_acuity->build_table_data();
echo $vision_acuity->build_table_html();
