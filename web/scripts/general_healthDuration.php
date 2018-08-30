<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$general_health = new duration_generator('general_health', $rank, $begin_date, $end_date);
$general_health->set_threshold(30);
$general_health->set_standard_deviation_scale(1);
$general_health->build_table_data();
echo $general_health->build_table_html();
