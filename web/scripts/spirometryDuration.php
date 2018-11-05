<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$spirometry = new duration_generator('spirometry', $rank, $begin_date, $end_date);

$smin = htmlspecialchars($_POST['stage-dur-min']);
$smax = htmlspecialchars($_POST['stage-dur-max']);

if(
  is_numeric($smin) &&
  is_numeric($smax)
  $smax>$smin)
{
  $spirometry->set_par_time_range(array($smin,$smax,$mmin,$mmax));
}

$spirometry->set_threshold(30);
$spirometry->set_standard_deviation_scale(1);
$spirometry->build_table_data();
echo $spirometry->build_table_html();
