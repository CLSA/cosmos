<?php
require_once 'qnaire_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$inhome_3 = new qnaire_generator(
  'inhome_3', $rank, $begin_date, $end_date);
$inhome_3->set_statistic($stat);
$inhome_3->build_table_data();
echo $inhome_3->build_table_html();
