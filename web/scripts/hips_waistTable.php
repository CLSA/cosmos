<?php
require_once 'hips_waist_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$hips_waist = new hips_waist_generator('hips_waist', $rank, $begin_date, $end_date);
$hips_waist->set_standard_deviation_scale(3);
$hips_waist->build_table_data();
echo $hips_waist->build_table_html();
