<?php

require_once 'common.php';

abstract class table_generator
{
  public function __construct( $_stage, $_rank, $_begin_date = null, $_end_date = null)
  {
    if(''==$_begin_date || ''==$_end_date ||
       !(preg_match('/^20(1|2)[0-9]-(0|1)[0-9]{1}-[0-3]{1}[0-9]{1}/',$_begin_date) &&
         preg_match('/^20(1|2)[0-9]-(0|1)[0-9]{1}-[0-3]{1}[0-9]{1}/',$_end_date)))
    {
      echo sprintf('error: invalid dates %s - %s',$_begin_date,$_end_date);
      die();
    }
    if( $_rank < 0 )
    {
      echo sprintf('error: invalid rank %s',$_rank);
      die();
    }
    if( '' == $_stage )
    {
      echo sprintf('error: missing stage name');
      die();
    }
    $this->name = $_stage;
    $this->rank = $_rank;
    $this->begin_date = $_begin_date;
    $this->end_date = $_end_date;

    $this->data = array();
    $this->indicator_keys = array();
    $this->percent_keys = array('total_skip','total_unexplained_missing','total_contraindicated');
    $this->total_keys = array();
    $this->site_list = array();

    $this->page_stage = '';
    $this->page_explanation = array();
  }

  abstract protected function build_data();

  protected function get_main_query()
  {
    return sprintf(
      'sum(case when strcmp(skip,"TechnicalProblem")=0 then 1 else 0 end) as total_skip_technical, '.
      'sum(case when strcmp(skip,"ParticipantDecision")=0 then 1 else 0 end) as total_skip_participant, '.
      'sum(case when strcmp(skip,"InterviewerDecision")=0 then 1 else 0 end) as total_skip_interviewer, '.
      'sum(case when strcmp(skip,"ModifiedVisit")=0 then 1 else 0 end) as total_skip_modified_visit, '.
      'sum(case when strcmp(skip,"SeeComment")=0 then 1 else 0 end) as total_skip_other, '.
      'sum(!isnull(skip)) as total_skip, '.
      'sum(missing) as total_unexplained_missing, '.
      'sum(contraindicated) as total_contraindicated, '.
      'sum(!isnull(t.name)) as total_tech, '.
      'sum(1) as total_interview '.
      'FROM interview i '.
      'join stage s on i.id=s.interview_id '.
      'join site on site.id=i.site_id '.
      'left join technician t on t.id=s.technician_id and t.site_id=site.id '.
      'where (start_date between "%s" and "%s") '.
      'and rank=%d '.
      'and s.name="%s" '.
      'group by site,tech', $this->begin_date, $this->end_date, $this->rank, $this->name);
  }

  public function set_page_stage($_stage)
  {
    $this->page_stage = $_stage;
  }

  public function set_percent_keys( $_keys )
  {
    if( 0 == count( $_keys ) )
    {
      die();
    }
    $this->percent_keys = $_keys;
  }

  public function set_indicator_keys( $_keys )
  {
    if( 0 == count( $_keys ) )
    {
      die();
    }
    $this->indicator_keys = $_keys;
  }

  public function indicator_keys_count()
  {
    return count($this->indicator_keys);
  }

  public function total_keys_count()
  {
    return count($this->total_keys);
  }

  public function build_table_data()
  {
    $this->build_data();

    $first = true;
    $this->total_keys = array();
    $this->site_data_list = array();
    foreach($this->data as $row)
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
          if(0==strpos($item,'total_')) $this->total_keys[]=$item;
        }
        $first=false;
        $this->site_data_list['ALL']['totals'] = array_combine($this->total_keys,array_fill(0,count($this->total_keys),0));
      }
      if(!array_key_exists($site,$this->site_data_list))
        $this->site_data_list[$site]['totals'] = array_combine($this->total_keys,array_fill(0,count($this->total_keys),0));
      foreach($this->total_keys as $key)
      {
        $this->site_data_list[$site]['totals'][$key]+=$row[$key];
        $this->site_data_list['ALL']['totals'][$key]+=$row[$key];
      }
      $this->site_data_list[$site]['technicians'][$tech]=$row;
    }

    $all_total = $this->site_data_list['ALL']['totals']['total_interview'];
    foreach( $this->site_data_list as $site=>$site_data )
    {
      $qc_total = 0;
      foreach( $this->indicator_keys as $key )
        $qc_total += $site_data['totals'][$key];
      if( 0 < $qc_total )
      {
        foreach( $this->indicator_keys as $key )
        {
          $value = $this->site_data_list[$site]['totals'][$key] ;
          if( 0 < $value )
            $this->site_data_list[$site]['totals'][$key] = sprintf( '%d</br>(%d)',
              $value, round( 100.0 * $value / $qc_total ) );
        }
      }
      $site_total = $site_data['totals']['total_interview'];
      if( 0 < $site_total )
      {
        foreach( $this->percent_keys as $key )
        {
          $value = $this->site_data_list[$site]['totals'][$key];
          if( 0 < $value )
            $this->site_data_list[$site]['totals'][$key] = sprintf( '%d</br>(%d)',
              $value, round( 100.0 * $value / $site_total ) );
        }
      }
      if( 'ALL' != $site && 0 < $all_total && 0 < $site_total )
      {
        $this->site_data_list[$site]['totals']['total_interview'] = sprintf( '%d</br>(%d)',
          $site_total, round( 100.0 * $site_total / $all_total ) );
      }
      if( !array_key_exists( 'technicians', $site_data ) ) continue;

      foreach( $site_data['technicians'] as $tech => $row )
      {
        $qc_total = 0;
        foreach( $this->indicator_keys as $key )
          $qc_total += $row[$key];
        if( 0 < $qc_total )
        {
          foreach( $this->indicator_keys as $key )
          {
            $value = $row[$key];
            if( 0 < $value )
              $this->site_data_list[$site]['technicians'][$tech][$key] = sprintf( '%d</br>(%d)',
                $value, round( 100.0 * $value / $qc_total ) );
          }
        }
        $total = $row['total_interview'];
        if( 0 < $total )
        {
          foreach( $this->percent_keys as $key )
          {
            $value = $row[$key];
            if( 0 < $value )
              $this->site_data_list[$site]['technicians'][$tech][$key] = sprintf( '%d</br>(%d)',
                $value, round( 100.0 * $value / $total ) );
          }

          if( 0 < $site_total )
          {
            $this->site_data_list[$site]['technicians'][$tech]['total_interview'] = sprintf( '%d</br>(%d)',
              $total, round( 100.0 * $total / $site_total ) );
          }
        }
      }
    }
  }

  public function build_table_html()
  {
    // set up the DataTable headers
    $ncol = count($this->total_keys)+1;
    $head_str_tech = "<tr><td>TECH</td>";
    $head_str_site = "<tr><td>SITE</td>";
    foreach($this->total_keys as $key)
    {
      $key_str = str_replace('_',' ',$key);
      $head_str_tech .= "<td>{$key_str}</td>";
      $head_str_site .= "<td>{$key_str}</td>";
    }
    $head_str_tech .= "</tr>";
    $head_str_site .= "</tr>";

    $num_qc_keys = count($this->indicator_keys);
    // set up the DataTable options for column group hiding
    $col_groups = array(
      'qc_group'=>range($num_qc_keys+1,$num_qc_keys+5),
      'skips'=>range(1,$num_qc_keys)
     );

    $hide_qc = sprintf( '[%s]', implode(',',$col_groups['qc_group']) );
    $hide_skip = sprintf( '[%s]', implode(',',$col_groups['skips']) );
    $page_heading = sprintf( '%s RESULTS - Wave %d (%s - %s)',$this->page_stage,$this->rank,$this->begin_date,$this->end_date);

    $explanation='';
    foreach($this->page_explanation as $item)
      $explanation .= "<li>$item</li>\n";

    $site_totals_block = '';
    foreach( $this->site_data_list as $site=>$site_data )
    {
      if('ALL'==$site) continue;
        $site_totals_block .= "<tr><td>$site</td>";
      foreach( $site_data['totals'] as $key=>$item )
        $site_totals_block .= "<td>$item</td>";
      $site_totals_block .= "</tr>";
    }

    $site_totals_final = '';
    foreach( $this->site_data_list['ALL']['totals'] as $key=>$item )
      $site_totals_final .= "<td>$item</td>";

    $tech_totals_block = '';
    foreach( $this->site_data_list as $site=>$site_data )
    {
      if('ALL'==$site) continue;
      $tech_totals_block .= "<table id='$site' class=\"clsa stripe cell-border order-column\" style=\"width:100%\">" .
           "<thead><tr><th colspan=$ncol>$site</th></tr>";
      $tech_totals_block .= $head_str_tech . "</thead><tbody>";
      foreach( $site_data['technicians'] as $tech=>$row )
      {
        if('NA'==$tech) continue;
        $tech_totals_block .=  "<tr><td>$tech</td>";
        foreach( $row as $key=>$item )
          $tech_totals_block .=  "<td>$item</td>";
        $tech_totals_block .=  "</tr>";
      }
      $tech_totals_block .=  "</tbody><tfoot><tr><td>TOTAL</td>";
      foreach( $site_data['totals'] as $key=>$item )
        $tech_totals_block .=  "<td>$item</td>";

      $tech_totals_block .= "</tr></tfoot></table>";
    }

return <<< HTML
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
          var page_heading = "$page_heading";
          var hide_qc = $hide_qc;
          var hide_skip = $hide_skip;
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
        <h3>$page_heading</h3>
        <ul>
          $explanation
        </ul>
        <!--
          build the main summary table
        -->
        <table id='summary' class="clsa stripe cell-border order-column" style="width:100%">
          <thead>
            <tr><th colspan=$ncol>SITE SUMMARY</th></tr>
              $head_str_site
          </thead>
          <tbody>
            $site_totals_block
          </tbody>
          <tfoot>
            <tr>
              <td>TOTAL</td>
                $site_totals_final
            </tr>
          </tfoot>
        </table>
        <!--
          build the sites and technician tables
        -->
        $tech_totals_block
      </body>
    </html>
HTML;
  }

  protected $data;

  protected $page_explanation;

  protected $page_stage;

  private $site_data_list;

  private $indicator_keys;

  private $percent_keys;

  private $total_keys;

  protected $rank;

  protected $begin_date;

  protected $end_date;

  protected $name;
}
?>
