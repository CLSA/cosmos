<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$blood = new duration_generator('blood', $rank, $begin_date, $end_date);
$blood->set_threshold(30);
$blood->set_standard_deviation_scale(1);
$blood->build_table_data();
echo $blood->build_table_html();
