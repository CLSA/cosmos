<?php
require_once 'dual_file_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$dual_hip_bone_density = new dual_file_generator(
  'dual_hip_bone_density', $rank, $begin_date, $end_date);
$dual_hip_bone_density->set_statistic($stat);
$dual_hip_bone_density->set_standard_deviation_scale(3);
$dual_hip_bone_density->set_file_scale(1024.0);
$dual_hip_bone_density->set_page_stage('DEXA DUAL HIP');
$dual_hip_bone_density->build_table_data();
echo $dual_hip_bone_density->build_table_html();
