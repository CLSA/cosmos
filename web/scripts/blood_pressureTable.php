<?php
require_once 'countable_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$blood_pressure = new countable_generator('blood_pressure', $rank, $begin_date, $end_date);
$blood_pressure->set_countable_target(5);
$blood_pressure->build_table_data();
echo $blood_pressure->build_table_html();
