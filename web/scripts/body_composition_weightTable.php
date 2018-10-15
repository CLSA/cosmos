<?php
require_once 'qnaire_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$body_composition_weight = new qnaire_generator(
  'body_composition_weight', $rank, $begin_date, $end_date);
$body_composition_weight->set_statistic($stat);
$body_composition_weight->set_module_refusal(true);
$body_composition_weight->build_table_data();
echo $body_composition_weight->build_table_html();
