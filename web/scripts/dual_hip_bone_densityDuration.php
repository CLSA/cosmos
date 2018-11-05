<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$dual_hip_bone_density = new duration_generator('dual_hip_bone_density', $rank, $begin_date, $end_date);

$smin = htmlspecialchars($_POST['stage-dur-min']);
$smax = htmlspecialchars($_POST['stage-dur-max']);

if(
  is_numeric($smin) &&
  is_numeric($smax)
  $smax>$smin)
{
  $dual_hip_bone_density->set_par_time_range(array($smin,$smax,$mmin,$mmax));
}

$dual_hip_bone_density->set_threshold(20);
$dual_hip_bone_density->set_standard_deviation_scale(1);
$dual_hip_bone_density->build_table_data();
echo $dual_hip_bone_density->build_table_html();
