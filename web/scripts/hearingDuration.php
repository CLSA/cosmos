<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$hearing = new duration_generator('hearing', $rank, $begin_date, $end_date);

$smin = htmlspecialchars($_POST['stage-dur-min']);
$smax = htmlspecialchars($_POST['stage-dur-max']);

if(
  is_numeric($smin) &&
  is_numeric($smax)
  $smax>$smin)
{
  $hearing->set_par_time_range(array($smin,$smax,$mmin,$mmax));
}

$hearing->set_threshold(30);
$hearing->set_standard_deviation_scale(1);
$hearing->build_table_data();
echo $hearing->build_table_html();
