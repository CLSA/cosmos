<?php
require_once 'timed_move_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$chair_rise = new timed_move_generator('chair_rise', $rank, $begin_date, $end_date);
$chair_rise->set_standard_deviation_scale(2);
$chair_rise->set_congruency_threshold(10);
$chair_rise->set_page_stage('CHAIR RISE');
$chair_rise->build_table_data();
echo $chair_rise->build_table_html();
