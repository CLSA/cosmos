<?php
require_once 'timed_move_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$tug = new timed_move_generator('tug', $rank, $begin_date, $end_date);
$tug->set_page_stage('TUG');
$tug->set_standard_deviation_scale(2);
$tug->set_congruency_threshold(10);
$tug->build_table_data();
echo $tug->build_table_html();
