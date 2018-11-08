<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$stroop_fas = new duration_generator('stroop_fas', $rank, $begin_date, $end_date);

$smin=null;
$smax=null;
$mmin=null;
$mmax=null;
$durations=array(
  'smin'=>'stage-dur-min',
  'smax'=>'stage-dur-max',
  'mmin'=>'module-dur-min',
  'mmax'=>'module-dur-max');
foreach($durations as $key=>$value)
{
  $data = explode(',',htmlspecialchars($_POST[$value]));
  if(is_array($data))
  {
    $data = str_replace(array(' h',' m',' s'),'',$data);
    if(3==count($data) && 0<array_sum($data))
      $$key = $data[0]*3600 + $data[1]*60 + $data[2];
  }
}

if(
  is_numeric($smin) &&
  is_numeric($smax) &&
  is_numeric($mmin) &&
  is_numeric($mmax) &&
  $smax>$smin && $mmax>$mmin)
{
  $stroop_fas->set_par_time_range(array($smin,$smax,$mmin,$mmax));
}

$stroop_fas->set_threshold(30);
$stroop_fas->set_standard_deviation_scale(1);
$stroop_fas->build_table_data();
echo $stroop_fas->build_table_html();
