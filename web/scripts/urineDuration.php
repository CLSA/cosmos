<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$urine = new duration_generator('urine', $rank, $begin_date, $end_date);
$urine->set_threshold(20);
$urine->set_standard_deviation_scale(1);
$urine->build_table_data();
echo $urine->build_table_html();
