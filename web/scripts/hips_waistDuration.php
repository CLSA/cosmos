<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$hips_waist = new duration_generator('hips_waist', $rank, $begin_date, $end_date);

$smin = htmlspecialchars($_POST['stage-dur-min']);
$smax = htmlspecialchars($_POST['stage-dur-max']);

if(
  is_numeric($smin) &&
  is_numeric($smax)
  $smax>$smin)
{
  $hips_waist->set_par_time_range(array($smin,$smax,$mmin,$mmax));
}

$hips_waist->set_standard_deviation_scale(1);
$hips_waist->build_table_data();
echo $hips_waist->build_table_html();
