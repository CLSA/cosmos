<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$blood_pressure = new duration_generator('blood_pressure', $rank, $begin_date, $end_date);
$blood_pressure->set_threshold(30);
$blood_pressure->set_standard_deviation_scale(1);
$blood_pressure->build_table_data();
echo $blood_pressure->build_table_html();
