<?php
require_once 'repeated_measure_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$weight = new repeated_measure_generator('weight', $rank, $begin_date, $end_date);
$weight->set_measurement_units('kg');
$weight->set_deviation_minimum(0.05);
$weight->set_deviation_maximum(0.1);
$weight->build_table_data();
echo $weight->build_table_html();
