<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stroop_fas = new duration_generator('stroop_fas', $rank, $begin_date, $end_date);
$stroop_fas->set_threshold(30);
$stroop_fas->set_standard_deviation_scale(1);
$stroop_fas->build_table_data();
echo $stroop_fas->build_table_html();
