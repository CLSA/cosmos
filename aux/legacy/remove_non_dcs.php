#!/usr/bin/php
<?php
require_once '../settings.ini.php';
require_once $SETTINGS['path']['PHP_UTIL'].'/database.class.php';
require_once $SETTINGS['path']['PHP_UTIL'].'/util.class.php';


$db = new database(
  $SETTINGS['db']['DB_SERV'],
  $SETTINGS['db']['DB_USER'],
  $SETTINGS['db']['DB_PASS'],
  $SETTINGS['db']['DB_NAME'] );


$rank=1;
$data = util::read_csv(sprintf('non_dcs_f%d.csv',$rank));
$rank++;
$sqln = sprintf('select count(*) from interview where rank=%d',$rank);
var_dump($db->get_one($sqln));

$num=0;
foreach($data as $item)
{
  //var_dump($item);
  //die();
  if($item['non_dcs']==1) continue;
  $num++;
  $sql = sprintf(
    'delete s.* from stage s '.
    'join interview i on i.id=s.interview_id '.
    'where uid="%s" '.
    'and rank=%d',$item['uid'], $rank);
  //$db->execute($sql);
}

util::out(sprintf('found %d non-dcs interviews',$num));
var_dump($db->get_one($sqln));


