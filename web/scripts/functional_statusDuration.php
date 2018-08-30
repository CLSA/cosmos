<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$functional_status = new duration_generator('functional_status', $rank, $begin_date, $end_date);
$functional_status->set_threshold(30);
$functional_status->set_standard_deviation_scale(1);
$functional_status->build_table_data();
echo $functional_status->build_table_html();
