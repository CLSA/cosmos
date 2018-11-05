<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$cdtt = new duration_generator('cdtt', $rank, $begin_date, $end_date);

$smin = htmlspecialchars($_POST['stage-dur-min']);
$smax = htmlspecialchars($_POST['stage-dur-max']);

if(
  is_numeric($smin) &&
  is_numeric($smax)
  $smax>$smin)
{
  $cdtt->set_par_time_range(array($smin,$smax,$mmin,$mmax));
}

$cdtt->set_threshold(20);
$cdtt->set_standard_deviation_scale(1);
$cdtt->build_table_data();
echo $cdtt->build_table_html();
