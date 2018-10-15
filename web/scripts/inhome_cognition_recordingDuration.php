<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$inhome_cognition_recording = new duration_generator('inhome_cognition_recording', $rank, $begin_date, $end_date);
$inhome_cognition_recording->set_standard_deviation_scale(1);
$inhome_cognition_recording->build_table_data();
echo $inhome_cognition_recording->build_table_html();
