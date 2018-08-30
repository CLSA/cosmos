<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$frax = new duration_generator('frax', $rank, $begin_date, $end_date);
$frax->set_threshold(2);
$frax->set_standard_deviation_scale(1);
$frax->build_table_data();
echo $frax->build_table_html();
