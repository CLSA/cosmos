<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$weight = new duration_generator('weight', $rank, $begin_date, $end_date);
$weight->set_standard_deviation_scale(1);
$weight->build_table_data();
echo $weight->build_table_html();
