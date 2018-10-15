<?php
require_once 'qnaire_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$osea = new qnaire_generator(
  'osea', $rank, $begin_date, $end_date);
$osea->set_statistic($stat);
$osea->set_module_refusal(true);
$osea->build_table_data();
echo $osea->build_table_html();
