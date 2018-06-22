<?php
require_once 'common.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

// query the db for the data we want

if(''==$begin_date || ''==$end_date ||
   !(preg_match('/^20(1|2)[0-9]-(0|1)[0-9]{1}-[0-3]{1}[0-9]{1}/',$begin_date) &&
     preg_match('/^20(1|2)[0-9]-(0|1)[0-9]{1}-[0-3]{1}[0-9]{1}/',$end_date)))
{
  echo sprintf('error: invalid dates %s - %s',$begin_date,$end_date);
  die();
}

$stdev_scale=2;
$use_mode=true;
$filesize_min=0;
$filesize_max=0;
if($use_mode)
{
  $minsz=0;
  $maxsz=0;
  $mode=0;
  $sql = sprintf(
    'select fsz, count(fsz) as freq '.
    'from ( '.
    '  select '.
    '   round( '.
    '    trim("}" from '.
    '      substring_index(qcdata, ":", -1))/1024.0) as fsz '.
    '  from interview i '.
    '  join stage s on i.id=s.interview_id '.
    '  where rank=%d '.
    '  and qcdata is not null '.
    '  and s.name="spine_bone_density" '.
    ') as t '.
    'where fsz>0 '.
    'group by fsz '.
    'order by freq desc limit 1', $rank);

  $res = $db->get_row( $sql );
  $mode = $res['fsz'];

  $sql = sprintf(
    'select min(fsz) as minsz, max(fsz) as maxsz '.
    'from ( '.
    '  select '.
    '  round( '.
    '    trim("}" from substring_index(qcdata, ":", -1))/1024.0) as fsz '.
    '  from interview i '.
    '  join stage s on i.id=s.interview_id '.
    '  where rank=%d '.
    '  and qcdata is not null '.
    '  and s.name="spine_bone_density" '.
    ') as t '.
    'where fsz>0', $rank);

  $res = $db->get_row( $sql );
  $minsz = $res['minsz'];
  $maxsz = $res['maxsz'];
  $filesize_min = max(intval(($minsz + 0.5*($mode-$minsz))*1024),0);
  $filesize_max = intval(($mode + 0.5*($maxsz-$mode))*1024);
}
else
{
  $avg=0;
  $stdev=0;
  $sql = sprintf(
    'select avg(fsz) as favg '.
    'from ( '.
    '  select '.
    '   round( '.
    '    trim("}" from '.
    '      substring_index(qcdata, ":", -1))/1024.0) as fsz '.
    '  from interview i '.
    '  join stage s on i.id=s.interview_id '.
    '  where rank=%d '.
    '  and qcdata is not null '.
    '  and s.name="spine_bone_density" '.
    ') as t '.
    'where fsz>0', $rank);

  $res = $db->get_row( $sql );
  $avg = $res['favg'];

  $sql = sprintf(
    'select stddev(fsz) as fstd '.
    'from ( '.
    '  select '.
    '  round( '.
    '    trim("}" from substring_index(qcdata, ":", -1))/1024.0) as fsz '.
    '  from interview i '.
    '  join stage s on i.id=s.interview_id '.
    '  where rank=%d '.
    '  and qcdata is not null '.
    '  and s.name="spine_bone_density" '.
    ') as t '.
    'where fsz>0', $rank);

  $res = $db->get_row( $sql );
  $stdev = $res['fstd'];
  $filesize_min = max(intval(($avg - $stdev_scale*$stdev)*1024),0);
  $filesize_max = intval(($avg + $stdev_scale*$stdev)*1024);
}

// build the main query
$sql =
  'select '.
  'ifnull(t.name,"NA") as tech, '.
  'site.name as site, ';

$sql .= sprintf(
  'sum(if(qcdata is null, 0, '.
  'if(trim("}" from substring_index(qcdata,":",-1))<%d,1,0))) as total_filesize_sub, ',$filesize_min);

$sql .= sprintf(
  'sum(if(qcdata is null, 0, '.
  'if(trim("}" from substring_index(qcdata,":",-1)) between %d and %d,1,0))) as total_filesize_par, ',$filesize_min,$filesize_max);

$sql .= sprintf(
  'sum(if(qcdata is null, 0, '.
  'if(trim("}" from substring_index(qcdata,":",-1))>%d,1,0))) as total_filesize_sup, ',$filesize_max);

$sql .= sprintf(
  'sum(case when strcmp(skip,"TechnicalProblem")=0 then 1 else 0 end) as total_skip_technical, '.
  'sum(case when strcmp(skip,"ParticipantDecision")=0 then 1 else 0 end) as total_skip_participant, '.
  'sum(case when strcmp(skip,"InterviewerDecision")=0 then 1 else 0 end) as total_skip_interviewer, '.
  'sum(case when strcmp(skip,"ModifiedVisit")=0 then 1 else 0 end) as total_skip_modified_visit, '.
  'sum(case when strcmp(skip,"SeeComment")=0 then 1 else 0 end) as total_skip_other, '.
  'sum(!isnull(skip)) as total_skip, '.
  'sum(if(missing,isnull(skip),0)) as total_unexplained_missing, '.
  'sum(contraindicated) as total_contraindicated, '.
  'sum(!isnull(t.name)) as total_tech, '.
  'sum(1) as total_interview '.
  'FROM interview i '.
  'join stage s on i.id=s.interview_id '.
  'join site on site.id=i.site_id '.
  'left join technician t on t.id=s.technician_id and t.site_id=site.id '.
  'where (start_date between "%s" and "%s") '.
  'and rank=%d '.
  'and s.name="spine_bone_density" '.
  'group by site,tech', $begin_date, $end_date, $rank);

$res = $db->get_all( $sql );
if(false===$res || !is_array($res))
{
  echo sprintf('error: failed query: %s', $db->get_last_error());
  echo $sql;
  die();
}

$first = true;
$total_keys = array();
$site_list = array();
foreach($res as $row)
{
  $site = $row['site'];
  unset($row['site']);
  $tech = $row['tech'];
  unset($row['tech']);
  if($first)
  {
    $keys = array_keys($row);
    foreach($keys as $item)
    {
      if(0==strpos($item,'total_')) $total_keys[]=$item;
    }
    $first=false;
    $site_list['ALL']['totals'] = array_combine($total_keys,array_fill(0,count($total_keys),0));
  }
  if(!array_key_exists($site,$site_list))
    $site_list[$site]['totals'] = array_combine($total_keys,array_fill(0,count($total_keys),0));
  foreach($total_keys as $key)
  {
    $site_list[$site]['totals'][$key]+=$row[$key];
    $site_list['ALL']['totals'][$key]+=$row[$key];
  }
  $site_list[$site]['technicians'][$tech]=$row;
}

$qc_keys=array('total_filesize_sub','total_filesize_par','total_filesize_sup');
$percent_keys = array('total_skip','total_unexplained_missing','total_contraindicated');
$all_total = $site_list['ALL']['totals']['total_interview'];
foreach($site_list as $site=>$site_data)
{
  $qc_total=0;
  foreach($qc_keys as $key)
    $qc_total += $site_data['totals'][$key];

  if(0<$qc_total)
  {
    foreach($qc_keys as $key)
    {
      $value = $site_list[$site]['totals'][$key] ;
      if( 0 < $value )
        $site_list[$site]['totals'][$key] = sprintf('%d</br>(%d)',
          $value,round(100.0*$value/$qc_total));
    }
  }
  $site_total = $site_data['totals']['total_interview'];
  if( 0 < $site_total )
  {
    foreach( $percent_keys as $key )
    {
      $value = $site_list[$site]['totals'][$key];
      if( 0 < $value )
        $site_list[$site]['totals'][$key] = sprintf('%d</br>(%d)',$value,round(100.0*$value/$site_total));
    }
  }
  if( 'ALL' != $site && 0 < $all_total && 0 < $site_total )
  {
    $site_list[$site]['totals']['total_interview'] = sprintf('%d</br>(%d)',$site_total,round(100.0*$site_total/$all_total));
  }
  if( !array_key_exists( 'technicians', $site_data ) ) continue;

  foreach( $site_data['technicians'] as $tech => $row )
  {
    $qc_total = 0;
    foreach( $qc_keys as $key )
      $qc_total += $row[$key];

    if( 0 < $qc_total )
    {
      foreach( $qc_keys as $key )
      {
        $value = $row[$key];
        if( 0 < $value )
          $site_list[$site]['technicians'][$tech][$key] = sprintf('%d</br>(%d)',
            $value,round(100.0*$value/$qc_total));
      }
    }
    $total = $row['total_interview'];
    if( 0 < $total )
    {
      foreach( $percent_keys as $key )
      {
        $value = $row[$key];
        if( 0 < $value )
          $site_list[$site]['technicians'][$tech][$key] =
            sprintf('%d</br>(%d)',$value,round(100.0*$value/$total));
      }

      if( 0 < $site_total )
      {
        $site_list[$site]['technicians'][$tech]['total_interview'] =
          sprintf('%d</br>(%d)',$total,round(100.0*$total/$site_total));
      }
    }
  }
}

// set up the DataTable headers
$ncol = count($total_keys)+1;
$head_str_tech = "<tr><td>TECH</td>";
$head_str_site = "<tr><td>SITE</td>";
foreach($total_keys as $key)
{
  $key_str = str_replace('_',' ',$key);
  $head_str_tech .= "<td>{$key_str}</td>";
  $head_str_site .= "<td>{$key_str}</td>";
}
$head_str_tech .= "</tr>";
$head_str_site .= "</tr>";

$num_qc_keys = count($qc_keys);
// set up the DataTable options for column group hiding
$col_groups = array(
  'qc_group'=>range($num_qc_keys+1,$num_qc_keys+5),
  'skips'=>range(1,$num_qc_keys)
 );

$hide_qc = sprintf( '[%s]', implode(',',$col_groups['qc_group']) );
$hide_skip = sprintf( '[%s]', implode(',',$col_groups['skips']) );
$page_heading = sprintf( 'DEXA SPINE RESULTS - Wave %d (%s - %s)',$rank,$begin_date,$end_date);
$page_explanation=array();
if($use_mode)
{
  $page_explanation[]=sprintf('<li>filesize sub: size < %d (min + 0.5 x (mode - min))</li>',$filesize_min);
  $page_explanation[]=sprintf('<li>filesize par: %d <= size <= %d</li>',$filesize_min,$filesize_max);
  $page_explanation[]=sprintf('<li>filesize sup: size > %d (mode + 0.5 x (max - mode))</li>',$filesize_max);
}
else
{
  $page_explanation[]=sprintf('<li>filesize sub: size < %d (mean - %s x SD)</li>',$filesize_min,$stdev_scale);
  $page_explanation[]=sprintf('<li>filesize par: %d <= size <= %d</li>',$filesize_min,$filesize_max);
  $page_explanation[]=sprintf('<li>filesize sup: size > %d (mean + %s x SD)</li>',$filesize_max,$stdev_scale);
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>CLSA-&Eacute;LCV QAC</title>
    <link rel="stylesheet" type="text/css" href="../css/qac.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" type="text/css" href="../css/datatables.min.css">
    <script type="text/javascript" src="datatables.min.js"></script>
    <script>
      var page_heading = <?php echo '"' . $page_heading . '"'; ?>;
      var hide_qc = <?php echo $hide_qc; ?>;
      var hide_skip = <?php echo $hide_skip; ?>;
      $( function() {
        function stripPercentData(data,row,column,node) {
          return data.replace(/(<br>\([\d*]+\))/g,'');
        }

        $( 'table.clsa' ).DataTable( {
          dom: 'Bfrtpl',
          buttons: [
            { extend: 'copyHtml5',
              footer: true,
              title: page_heading,
              exportOptions: {
                format: {
                  footer: stripPercentData,
                  body: stripPercentData
                }
              }
            },
            { extend: 'excelHtml5',
              footer: true,
              title: page_heading,
              exportOptions: {
                format: {
                  footer: stripPercentData,
                  body: stripPercentData
                }
              }
            },
            { extend: 'csvHtml5',
              footer: true,
              title: page_heading,
              exportOptions: {
                format: {
                  footer: stripPercentData,
                  body: stripPercentData
                }
              }
            },
            {
              extend: 'colvisGroup',
              text: 'QC Metric',
              show: hide_skip,
              hide: hide_qc
            },
            {
              extend: 'colvisGroup',
              text: 'Skips',
              hide: hide_skip,
              show: hide_qc
            },
            {
              extend: 'colvisGroup',
              text: 'Show All',
              show: ':hidden'
            }
          ]
        });
      });
    </script>
  </head>
  <body>
    <h3><?php echo $page_heading?></h3>
    <ul>
      <?php
        foreach($page_explanation as $item)
          echo $item;
      ?>
    </ul>
    <!--build the main summary table-->
    <table id='summary' class="clsa stripe cell-border order-column" style="width:100%">
      <thead>
        <tr><?php echo"<th colspan={$ncol}>SITE SUMMARY</th>"?></tr>
        <?php echo $head_str_site?>
      </thead>
      <tbody>
        <?php
          foreach( $site_list as $site=>$site_data )
          {
            if('ALL'==$site) continue;
            echo "<tr><td>{$site}</td>";
            foreach( $site_data['totals'] as $key=>$item )
              echo "<td>{$item}</td>";
            echo "</tr>";
          }
        ?>
      </tbody>
      <tfoot>
        <tr>
          <td>TOTAL</td>
          <?php
            foreach( $site_list['ALL']['totals'] as $key=>$item )
              echo "<td>{$item}</td>";
          ?>
        </tr>
      </tfoot>
    </table>
    <!--build the sites and technician tables-->

    <?php
      foreach( $site_list as $site=>$site_data )
      {
        if('ALL'==$site) continue;
        echo "<table id='{$site}' class=\"clsa stripe cell-border order-column\" style=\"width:100%\">" .
             "<thead><tr><th colspan={$ncol}>{$site}</th></tr>";
        echo $head_str_tech . "</thead><tbody>";
        foreach( $site_data['technicians'] as $tech=>$row )
        {
          if('NA'==$tech) continue;
          echo "<tr><td>{$tech}</td>";
          foreach( $row as $key=>$item )
            echo "<td>{$item}</td>";
          echo "</tr>";
        }
        echo "</tbody><tfoot><tr><td>TOTAL</td>";
        foreach( $site_data['totals'] as $key=>$item )
          echo "<td>{$item}</td>";

        echo "</tr></tfoot></table>";
      }
    ?>

  </body>
</html>
