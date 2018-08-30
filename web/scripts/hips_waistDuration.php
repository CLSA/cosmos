<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$spirometry = new duration_generator('spirometry', $rank, $begin_date, $end_date);
$spirometry->set_standard_deviation_scale(1);
$spirometry->build_table_data();
echo $spirometry->build_table_html();
