<?php
require_once 'qnaire_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$contraindications = new qnaire_generator(
  'contraindications', $rank, $begin_date, $end_date);
$contraindications->set_statistic($stat);
$contraindications->build_table_data();
echo $contraindications->build_table_html();
