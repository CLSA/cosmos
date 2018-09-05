<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$sitting_height = new duration_generator('sitting_height', $rank, $begin_date, $end_date);
$sitting_height->set_threshold(10);
$sitting_height->set_standard_deviation_scale(1);
$sitting_height->build_table_data();
echo $sitting_height->build_table_html();