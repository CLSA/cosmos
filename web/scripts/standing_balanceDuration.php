<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$standing_balance = new duration_generator('standing_balance', $rank, $begin_date, $end_date);
$standing_balance->set_threshold(10);
$standing_balance->set_standard_deviation_scale(1);
$standing_balance->build_table_data();
echo $standing_balance->build_table_html();
