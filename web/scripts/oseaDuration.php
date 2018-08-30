<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$osea = new duration_generator('osea', $rank, $begin_date, $end_date);
$osea->set_standard_deviation_scale(1);
$osea->build_table_data();
echo $osea->build_table_html();
