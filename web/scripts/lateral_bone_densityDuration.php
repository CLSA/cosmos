<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$lateral_bone_density = new duration_generator('lateral_bone_density', $rank, $begin_date, $end_date);

$smin=null;
$smax=null;
$durations=array(
  'smin'=>'stage-dur-min',
  'smax'=>'stage-dur-max');
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
  is_numeric($smax)
  $smax>$smin)
{
  $lateral_bone_density->set_par_time_range(array($smin,$smax));
}

$lateral_bone_density->set_threshold(20);
$lateral_bone_density->set_standard_deviation_scale(1);
$lateral_bone_density->build_table_data();
echo $lateral_bone_density->build_table_html();
