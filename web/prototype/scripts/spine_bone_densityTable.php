<?php
require_once 'single_file_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$spine_bone_density = new single_file_generator(
  'spine_bone_density', $rank, $begin_date, $end_date);
$spine_bone_density->set_file_scale(1024.0);
$spine_bone_density->set_statistic($stat);
$spine_bone_density->set_standard_deviation_scale(2);
$spine_bone_density->set_page_stage('DEXA AP SPINE');
$spine_bone_density->build_table_data();
echo $spine_bone_density->build_table_html();
