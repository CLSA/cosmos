<?php
require_once 'spirometry_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$spirometry = new spirometry_generator('spirometry', $rank, $begin_date, $end_date);
$spirometry->build_table_data();
$spirometry->build_plot_html();
