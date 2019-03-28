<?php
require_once 'qnaire_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$osonly = new qnaire_generator(
  'osonly', $rank, $begin_date, $end_date);
$osonly->set_statistic($stat);
$osonly->set_module_refusal(true);
$osonly->build_table_data();
echo $osonly->build_table_html();
