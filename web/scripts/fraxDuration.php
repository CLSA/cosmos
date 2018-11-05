<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$frax = new duration_generator('frax', $rank, $begin_date, $end_date);

$smin = htmlspecialchars($_POST['stage-dur-min']);
$smax = htmlspecialchars($_POST['stage-dur-max']);

if(
  is_numeric($smin) &&
  is_numeric($smax)
  $smax>$smin)
{
  $frax->set_par_time_range(array($smin,$smax,$mmin,$mmax));
}

$frax->set_threshold(2);
$frax->set_standard_deviation_scale(1);
$frax->build_table_data();
echo $frax->build_table_html();
