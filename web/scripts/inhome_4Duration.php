<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$inhome_4 = new duration_generator('inhome_4', $rank, $begin_date, $end_date);
$inhome_4->set_threshold(100);
$inhome_4->set_standard_deviation_scale(1);
$inhome_4->build_table_data();
echo $inhome_4->build_table_html();
