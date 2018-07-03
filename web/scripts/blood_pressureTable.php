<?php
require_once 'trial_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$blood_pressure = new trial_generator('blood_pressure', $rank, $begin_date, $end_date);
$blood_pressure->set_page_stage( 'BLOOD PRESSURE' );
$blood_pressure->set_trial_target(5);
$blood_pressure->build_table_data();
echo $blood_pressure->build_table_html();
