<?php
require_once 'qnaire_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stat='mean';
if(array_key_exists('stat-option',$_POST))
  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$functional_status = new qnaire_generator(
  'functional_status', $rank, $begin_date, $end_date);

$functional_status->set_statistic($stat);
$functional_status->set_standard_deviation_scale(2);
$functional_status->set_page_stage('FUNCTIONAL STATUS');
$functional_status->build_table_data();
echo $functional_status->build_table_html();
