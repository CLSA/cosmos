<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$event_pmt = new duration_generator('event_pmt', $rank, $begin_date, $end_date);
$event_pmt->set_threshold(45);
$event_pmt->set_standard_deviation_scale(1);
$event_pmt->build_table_data();
echo $event_pmt->build_table_html();
