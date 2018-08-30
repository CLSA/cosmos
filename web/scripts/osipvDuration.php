<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$osipv = new duration_generator('osipv', $rank, $begin_date, $end_date);
$osipv->set_threshold(30);
$osipv->set_standard_deviation_scale(1);
$osipv->build_table_data();
echo $osipv->build_table_html();
