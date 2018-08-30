<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$tug = new duration_generator('tug', $rank, $begin_date, $end_date);
$tug->set_threshold(5);
$tug->set_standard_deviation_scale(1);
$tug->build_table_data();
echo $tug->build_table_html();
