<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$spine_bone_density = new duration_generator('spine_bone_density', $rank, $begin_date, $end_date);
$spine_bone_density->set_threshold(20);
$spine_bone_density->set_standard_deviation_scale(1);
$spine_bone_density->build_table_data();
echo $spine_bone_density->build_table_html();
