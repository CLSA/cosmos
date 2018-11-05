<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$lateral_bone_density = new duration_generator('lateral_bone_density', $rank, $begin_date, $end_date);

$smin = htmlspecialchars($_POST['stage-dur-min']);
$smax = htmlspecialchars($_POST['stage-dur-max']);

if(
  is_numeric($smin) &&
  is_numeric($smax)
  $smax>$smin)
{
  $lateral_bone_density->set_par_time_range(array($smin,$smax,$mmin,$mmax));
}

$lateral_bone_density->set_threshold(20);
$lateral_bone_density->set_standard_deviation_scale(1);
$lateral_bone_density->build_table_data();
echo $lateral_bone_density->build_table_html();
