<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$time_based_pmt = new duration_generator('time_based_pmt', $rank, $begin_date, $end_date);
$time_based_pmt->set_standard_deviation_scale(1);
$time_based_pmt->build_table_data();
echo $time_based_pmt->build_table_html();
