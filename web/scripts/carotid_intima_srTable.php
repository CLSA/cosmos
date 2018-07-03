<?php
require_once 'carotid_intima_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$carotid_intima = new carotid_intima_generator('carotid_intima', $rank, $begin_date, $end_date);
$carotid_intima->set_file_type('sr');
$carotid_intima->set_statistic($stat);
$carotid_intima->set_standard_deviation_scale(1);
$carotid_intima->build_table_data();
echo $carotid_intima->build_table_html();
