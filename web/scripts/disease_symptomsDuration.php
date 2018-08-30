<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$disease_symptoms = new duration_generator('disease_symptoms', $rank, $begin_date, $end_date);
$disease_symptoms->set_standard_deviation_scale(1);
$disease_symptoms->build_table_data();
echo $disease_symptoms->build_table_html();
