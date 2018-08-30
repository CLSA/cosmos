<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$chair_rise = new duration_generator('chair_rise', $rank, $begin_date, $end_date);
$chair_rise->set_threshold(10);
$chair_rise->set_standard_deviation_scale(1);
$chair_rise->build_table_data();
echo $chair_rise->build_table_html();
