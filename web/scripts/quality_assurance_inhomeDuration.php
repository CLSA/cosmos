<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$quality_assurance_inhome = new duration_generator('quality_assurance_inhome', $rank, $begin_date, $end_date);
$quality_assurance_inhome->set_threshold(15);
$quality_assurance_inhome->set_standard_deviation_scale(1);
$quality_assurance_inhome->build_table_data();
echo $quality_assurance_inhome->build_table_html();
