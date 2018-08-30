<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$ecg = new duration_generator('ecg', $rank, $begin_date, $end_date);
$ecg->set_standard_deviation_scale(1);
$ecg->build_table_data();
echo $ecg->build_table_html();
