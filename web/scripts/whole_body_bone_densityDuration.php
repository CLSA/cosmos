<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$whole_body_bone_density = new duration_generator('whole_body_bone_density', $rank, $begin_date, $end_date);
$whole_body_bone_density->set_threshold(20);
$whole_body_bone_density->set_standard_deviation_scale(1);
$whole_body_bone_density->build_table_data();
echo $whole_body_bone_density->build_table_html();
