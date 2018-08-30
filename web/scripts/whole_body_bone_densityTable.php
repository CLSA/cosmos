<?php
require_once 'single_file_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$whole_body_bone_density = new single_file_generator(
  'whole_body_bone_density', $rank, $begin_date, $end_date);
$whole_body_bone_density->set_file_scale(1024.0);
$whole_body_bone_density->set_statistic($stat);
$whole_body_bone_density->set_standard_deviation_scale(2);
$whole_body_bone_density->set_page_stage('DEXA WHOLE BODY');
$whole_body_bone_density->build_table_data();
echo $whole_body_bone_density->build_table_html();
