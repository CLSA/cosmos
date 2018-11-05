<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$four_metre_walk = new duration_generator('four_metre_walk', $rank, $begin_date, $end_date);

$smin = htmlspecialchars($_POST['stage-dur-min']);
$smax = htmlspecialchars($_POST['stage-dur-max']);

if(
  is_numeric($smin) &&
  is_numeric($smax)
  $smax>$smin)
{
  $four_metre_walk->set_par_time_range(array($smin,$smax,$mmin,$mmax));
}

$four_metre_walk->set_threshold(10);
$four_metre_walk->set_standard_deviation_scale(1);
$four_metre_walk->build_table_data();
echo $four_metre_walk->build_table_html();
