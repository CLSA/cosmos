<?php
require_once 'vision_acuity_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$vision_acuity = new vision_acuity_generator('vision_acuity', $rank, $begin_date, $end_date);
$vision_acuity->build_table_data();
echo $vision_acuity->build_table_html();
