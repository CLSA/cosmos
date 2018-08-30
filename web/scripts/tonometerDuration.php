<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$tonometer = new duration_generator('tonometer', $rank, $begin_date, $end_date);
$tonometer->set_threshold(10);
$tonometer->set_standard_deviation_scale(1);
$tonometer->build_table_data();
echo $tonometer->build_table_html();
