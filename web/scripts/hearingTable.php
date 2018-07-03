<?php
require_once 'hearing_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$hearing = new hearing_generator('hearing', $rank, $begin_date, $end_date);
$hearing->build_table_data();
echo $hearing->build_table_html();
