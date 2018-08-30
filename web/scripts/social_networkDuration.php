<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$social_network = new duration_generator('social_network', $rank, $begin_date, $end_date);
$social_network->set_threshold(40);
$social_network->set_standard_deviation_scale(1);
$social_network->build_table_data();
echo $social_network->build_table_html();
