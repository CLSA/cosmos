<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$conclusion_questionnaire = new duration_generator('conclusion_questionnaire', $rank, $begin_date, $end_date);
$conclusion_questionnaire->set_threshold(10);
$conclusion_questionnaire->set_standard_deviation_scale(1);
$conclusion_questionnaire->build_table_data();
echo $conclusion_questionnaire->build_table_html();
