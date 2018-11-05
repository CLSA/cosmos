<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$body_composition_weight = new duration_generator('body_composition_weight', $rank, $begin_date, $end_date);

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
  $body_composition_weight->set_par_time_range(array($smin,$smax,$mmin,$mmax));
}

$body_composition_weight->set_threshold(20);
$body_composition_weight->set_standard_deviation_scale(1);
$body_composition_weight->build_table_data();
echo $body_composition_weight->build_table_html();
