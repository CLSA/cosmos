<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$body_composition_weight = new duration_generator('body_composition_weight', $rank, $begin_date, $end_date);
$body_composition_weight->set_threshold(20);
$body_composition_weight->set_standard_deviation_scale(1);
$body_composition_weight->build_table_data();
echo $body_composition_weight->build_table_html();
