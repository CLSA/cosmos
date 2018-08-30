<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$neuropsychological_battery = new duration_generator('neuropsychological_battery', $rank, $begin_date, $end_date);
$neuropsychological_battery->set_threshold(30);
$neuropsychological_battery->set_standard_deviation_scale(1);
$neuropsychological_battery->build_table_data();
echo $neuropsychological_battery->build_table_html();
