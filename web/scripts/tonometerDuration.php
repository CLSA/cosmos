<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$tonometer = new duration_generator('tonometer', $rank, $begin_date, $end_date);

$smin = htmlspecialchars($_POST['stage-dur-min']);
$smax = htmlspecialchars($_POST['stage-dur-max']);

if(
  is_numeric($smin) &&
  is_numeric($smax)
  $smax>$smin)
{
  $tonometer->set_par_time_range(array($smin,$smax,$mmin,$mmax));
}

$tonometer->set_threshold(10);
$tonometer->set_standard_deviation_scale(1);
$tonometer->build_table_data();
echo $tonometer->build_table_html();
