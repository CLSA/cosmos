<?php
require_once 'qnaire_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$event_pmt = new qnaire_generator(
  'event_pmt', $rank, $begin_date, $end_date);
$event_pmt->set_statistic($stat);
$event_pmt->set_module_refusal(true);
$event_pmt->build_table_data();
echo $event_pmt->build_table_html();
