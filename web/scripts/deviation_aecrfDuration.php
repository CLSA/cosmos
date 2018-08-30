<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$deviation_aecrf = new duration_generator('deviation_aecrf', $rank, $begin_date, $end_date);
$deviation_aecrf->set_threshold(10);
$deviation_aecrf->set_standard_deviation_scale(1);
$deviation_aecrf->build_table_data();
echo $deviation_aecrf->build_table_html();
