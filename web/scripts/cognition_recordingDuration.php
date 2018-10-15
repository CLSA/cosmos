<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$cognition_recording = new duration_generator('cognition_recording', $rank, $begin_date, $end_date);
$cognition_recording->set_standard_deviation_scale(1);
$cognition_recording->build_table_data();
echo $cognition_recording->build_table_html();
