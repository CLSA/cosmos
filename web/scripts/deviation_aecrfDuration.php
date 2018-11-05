<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$deviation_aecrf = new duration_generator('deviation_aecrf', $rank, $begin_date, $end_date);

$smin = htmlspecialchars($_POST['stage-dur-min']);
$smax = htmlspecialchars($_POST['stage-dur-max']);
$mmin = htmlspecialchars($_POST['module-dur-min']);
$mmax = htmlspecialchars($_POST['module-dur-max']);

if(
  is_numeric($smin) &&
  is_numeric($smax) &&
  is_numeric($mmin) &&
  is_numeric($mmax) &&
  $smax>$smin && $mmax>$mmin)
{
  $deviation_aecrf->set_par_time_range(array($smin,$smax,$mmin,$mmax));
}

$deviation_aecrf->set_threshold(10);
$deviation_aecrf->set_standard_deviation_scale(1);
$deviation_aecrf->build_table_data();
echo $deviation_aecrf->build_table_html();
