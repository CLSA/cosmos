<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$forearm_bone_density = new duration_generator('forearm_bone_density', $rank, $begin_date, $end_date);
$forearm_bone_density->set_threshold(20);
$forearm_bone_density->set_standard_deviation_scale(1);
$forearm_bone_density->build_table_data();
echo $forearm_bone_density->build_table_html();
