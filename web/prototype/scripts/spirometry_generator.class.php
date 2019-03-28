<?php
require_once 'table_generator.class.php';

class spirometry_generator extends table_generator
{
  public function __construct($_stage, $_rank, $_begin_date = null, $_end_date = null)
  {
    parent::__construct($_stage, $_rank, $_begin_date, $_end_date);

    $this->page_stage = 'SPIROMETRY';
  }

  protected function build_data()
  {
    global $db;

    $grade_list=array(
      1=>array('A','B','C','C1','C2','D1','D2','F'),
      2=>array('A','B'));

    if( 2 == $this->rank )
      $grades = $grade_list[$this->rank];
    else
      $grades = $grade_list[1];

    $this->indicator_keys=array();

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    foreach($grades as $grade)
    {
      $sql .= sprintf('sum(case when strcmp(qcdata,"{grade:%s}")=0 then 1 else 0 end) as total_%s, ',$grade,$grade);
      $this->indicator_keys[] = sprintf('total_%s',$grade);
    }

    $sql .= $this->get_main_query();

    $res = $db->get_all( $sql );
    if(false===$res || !is_array($res))
    {
      echo sprintf('error: failed query: %s', $db->get_last_error());
      echo $sql;
      die();
    }
    $this->data = $res;

    $this->page_explanation[]=sprintf('Grades: %s',implode(', ',$grades));
  }

  public function build_plot_html()
  {
    $plot = new PHPlot;
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

    $data = array(); 
    foreach($this->site_data_list as $site=>$site_data)
    {
      if('ALL' == $site) continue;
      $row_data = array($site);
      foreach( $this->indicator_keys as $key )
      {
        $row_data[] = $site_data['totals'][$key];
      }
      $data[] = $row_data;
    }

    $plot = new PHPlot(800, 600);
    $plot->SetImageBorderType('plain');

    $plot->SetPlotType('stackedbars');
    $plot->SetDataType('text-data');
    $plot->SetDataValues($data);

    $plot->SetTitle('Spirometry Grades by Site');
    $plot->SetYTitle('Interviews');

    # No shading:
    $plot->SetShading(0);

    $plot->SetLegend($this->indicator_keys);
    # Make legend lines go bottom to top, like the bar segments (PHPlot > 5.4.0)
    $plot->SetLegendReverse(True);

    $plot->SetXTickLabelPos('none');
    $plot->SetXTickPos('none');

    $plot->DrawGraph();
  }
}
