<?php
require_once 'repeated_measure_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$sitting_height = new repeated_measure_generator('sitting_height', $rank, $begin_date, $end_date);
$sitting_height->set_page_stage( 'SITTING HEIGHT' );
$sitting_height->set_measurement_units('cm');
$sitting_height->set_deviation_minimum(0.01);
$sitting_height->set_deviation_maximum(0.1);
$sitting_height->build_table_data();
echo $sitting_height->build_table_html();
