<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$osonly = new duration_generator('osonly', $rank, $begin_date, $end_date);
$osonly->set_standard_deviation_scale(1);
$osonly->build_table_data();
echo $osonly->build_table_html();
