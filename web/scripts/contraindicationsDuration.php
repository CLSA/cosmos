<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$contraindications = new duration_generator('contraindications', $rank, $begin_date, $end_date);
$contraindications->set_threshold(30);
$contraindications->set_standard_deviation_scale(1);
$contraindications->build_table_data();
echo $contraindications->build_table_html();
