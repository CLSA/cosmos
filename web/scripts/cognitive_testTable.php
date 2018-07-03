<?php
require_once 'single_file_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$cognitive_test = new single_file_generator(
  'cognitive_test', $rank, $begin_date, $end_date);

$cognitive_test->set_statistic($stat);
$cognitive_test->set_standard_deviation_scale(2);
$cognitive_test->build_table_data();
echo $cognitive_test->build_table_html();
