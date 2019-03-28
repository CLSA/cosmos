<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$inhome_cognition_recording = new duration_generator('inhome_cognition_recording', $rank, $begin_date, $end_date);

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
  $inhome_cognition_recording->set_par_time_range(array($smin,$smax,$mmin,$mmax));
}

$inhome_cognition_recording->set_standard_deviation_scale(1);
$inhome_cognition_recording->build_table_data();
echo $inhome_cognition_recording->build_table_html();
