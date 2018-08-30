<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$hearing = new duration_generator('hearing', $rank, $begin_date, $end_date);
$hearing->set_threshold(30);
$hearing->set_standard_deviation_scale(1);
$hearing->build_table_data();
echo $hearing->build_table_html();
