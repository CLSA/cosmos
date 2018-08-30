<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$carotid_intima = new duration_generator('carotid_intima', $rank, $begin_date, $end_date);
$carotid_intima->set_standard_deviation_scale(1);
$carotid_intima->build_table_data();
echo $carotid_intima->build_table_html();
