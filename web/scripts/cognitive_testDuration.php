<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$cognitive_test = new duration_generator('cognitive_test', $rank, $begin_date, $end_date);
$cognitive_test->set_threshold(10);
$cognitive_test->set_standard_deviation_scale(1);
$cognitive_test->build_table_data();
echo $cognitive_test->build_table_html();
