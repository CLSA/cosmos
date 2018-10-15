<?php
require_once 'qnaire_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$osipv = new qnaire_generator(
  'osipv', $rank, $begin_date, $end_date);
$osipv->set_module_refusal(true);
$osipv->set_statistic($stat);
$osipv->build_table_data();
echo $osipv->build_table_html();
