<?php
require_once 'retinal_scan_generator.class.php';

$begin_date = '2012-05-29';//htmlspecialchars($_POST['from']);
$end_date = '2015-07-09';//htmlspecialchars($_POST['to']);
$rank = 1;//htmlspecialchars($_POST['rank']);

$stat='mean';
//if(array_key_exists('stat-option',$_POST))
//  $stat  = 'mode'==htmlspecialchars($_POST['stat-option']) ? 'mode' : 'mean';

$retinal_scan = new retinal_scan_generator('retinal_scan',$rank,$begin_date,$end_date);
$retinal_scan->set_statistic($stat);
$retinal_scan->set_standard_deviation_scale(2);
$retinal_scan->build_table_data();
echo $retinal_scan->build_table_html();
