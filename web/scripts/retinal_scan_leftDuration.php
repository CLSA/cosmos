<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$retinal_scan_left = new duration_generator('retinal_scan_left', $rank, $begin_date, $end_date);

$smin = htmlspecialchars($_POST['stage-dur-min']);
$smax = htmlspecialchars($_POST['stage-dur-max']);

if(
  is_numeric($smin) &&
  is_numeric($smax)
  $smax>$smin)
{
  $retinal_scan_left->set_par_time_range(array($smin,$smax,$mmin,$mmax));
}

$retinal_scan_left->set_threshold(20);
$retinal_scan_left->set_standard_deviation_scale(1);
$retinal_scan_left->build_table_data();
echo $retinal_scan_left->build_table_html();
