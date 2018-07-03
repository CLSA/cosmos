<?php
require_once 'trial_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$grip_strength = new trial_generator('grip_strength', $rank, $begin_date, $end_date);
$grip_strength->set_page_stage( 'GRIP STRENGTH' );
$grip_strength->set_trial_target(3);
$grip_strength->build_table_data();
echo $grip_strength->build_table_html();
