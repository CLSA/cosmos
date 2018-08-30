<?php
require_once 'countable_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$conclusion_questionnaire = new countable_generator('conclusion_questionnaire', $rank, $begin_date, $end_date);
$conclusion_questionnaire->set_countable_label('document');
$conclusion_questionnaire->set_countable_target(3);
$conclusion_questionnaire->build_table_data();
echo $conclusion_questionnaire->build_table_html();
