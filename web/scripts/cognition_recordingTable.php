<?php
require_once 'multiple_file_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$cognition_recording = new multiple_file_generator('cognition_recording', $rank, $begin_date, $end_date);
$cognition_recording->set_statistic($stat);
$cognition_recording->set_standard_deviation_scale(2);
$cognition_recording->set_file_list( 
  array('DOT'=>1,'WOR'=>2,'COL'=>3,'F'=>4,'A'=>5,'S'=>6)
);
$cognition_recording->build_table_data();
echo $cognition_recording->build_table_html();
