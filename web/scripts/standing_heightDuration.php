<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$standing_height = new duration_generator('standing_height', $rank, $begin_date, $end_date);
$standing_height->set_threshold(10);
$standing_height->set_standard_deviation_scale(1);
$standing_height->build_table_data();
echo $standing_height->build_table_html();
