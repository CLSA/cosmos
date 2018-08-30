<?php
require_once (dirname(__FILE__).'/../settings.ini.php');
require_once $SETTINGS['path']['APPLICATION'].'/web/scripts/common.php';

  $rank = 3;
  $stage= 'spirometry';

    $sql = sprintf(
      'select duration from stage s '.
      'join interview i on i.id=s.interview_id '.
      'where rank=%d '.
      'and s.name="%s" '.
      'and duration is not null '.
      'limit 1',$rank,$stage);
    $res = $db->get_one($sql);
    $res = explode(',',trim($res,'{}'));
    var_dump($res);

    $keys =array();
    foreach($res as $value)
    {
      $value=explode(':',$value);
      $keys[]=array_shift($value);
    }
    var_dump($keys);

    $t_avg=0;
    $t_std=0;

    $sql = sprintf(
        'select avg(d_time) as d_avg, stddev(d_time) as d_std '.
        'from ( '.
        '  select cast(trim("}" from substring_index(duration, ":", -1)) as decimal) as d_time '.
        '  from interview i '.
        '  join stage s on i.id=s.interview_id '.
        '  where rank=%d '.
        '  and duration is not null '.
        '  and s.name="%s" '.
        ') as t', $rank, $stage);

    $res = $db->get_row( $sql );
 var_dump($res);



?>
