<?php
require_once 'qnaire_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$inhome_1 = new qnaire_generator(
  'inhome_1', $rank, $begin_date, $end_date);
$inhome_1->set_statistic($stat);
$inhome_1->set_module_refusal(true);
$inhome_1->build_table_data();
echo $inhome_1->build_table_html();
