<?php
require_once 'multiple_file_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$inhome_cognition_recording = new multiple_file_generator('inhome_cognition_recording', $rank, $begin_date, $end_date);
$inhome_cognition_recording->set_statistic($stat);
$inhome_cognition_recording->set_standard_deviation_scale(2);
$inhome_cognition_recording->set_file_list( 
  array('REYI'=>1,'REYII'=>6,'ANIMAL'=>2,'COUNT'=>3,'ALPHA'=>4,'ALTERNATE'=>5)
);
$inhome_cognition_recording->build_table_data();
echo $inhome_cognition_recording->build_table_html();
