<?php
require_once 'repeated_measure_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$standing_height = new repeated_measure_generator('standing_height', $rank, $begin_date, $end_date);
$standing_height->set_measurement_units('cm');
$standing_height->set_deviation_minimum(0.01);
$standing_height->set_deviation_maximum(0.1);
$standing_height->build_table_data();
echo $standing_height->build_table_html();
