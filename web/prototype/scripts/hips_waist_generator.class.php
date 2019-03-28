<?php
require_once 'table_generator.class.php';

class hips_waist_generator extends table_generator
{
  public function __construct($_stage, $_rank, $_begin_date = null, $_end_date = null)
  {
    parent::__construct($_stage, $_rank, $_begin_date, $_end_date);

    $this->indicator_keys=array(
      'total_skin','total_one_layer','total_two_layers','total_ratio_sub','total_ratio_par','total_ratio_sup');
    $this->standard_deviation_scale = 3;
    $this->page_stage = 'HIPS WAIST';
  }

  protected function build_data()
  {
    global $db;

    // build the main query

    $sql = sprintf(
      'select avg(ratio) as r_avg, stddev(ratio) as r_std '.
      'from ( '.
      '  select cast( trim("}" from '.
      '      substring_index( '.
      '        substring_index( '.
      '          qcdata, ",", 4), ":", -1)) as decimal(10,3)) as ratio '.
      '  from interview i '.
      '  join stage s on i.id=s.interview_id '.
      '  where rank=%d '.
      '  and qcdata is not null '.
      '  and s.name="%s" '.
      ') as t', $this->rank, $this->name);

    $res = $db->get_row( $sql );
    if( false === $res )
    {
      echo sprintf('failed to get ratio data: %s', $db->get_last_error() );
      echo $sql;
      die();
    }
    $avg = $res['r_avg'];
    $std = $res['r_std'];

    $ratio_min = max(round($avg - $this->standard_deviation_scale*$std,3),0);
    $ratio_max = round($avg + $this->standard_deviation_scale*$std,3);

    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    $sql .=
      'sum(if(qcdata is null, 0, '.
      'if(strcmp("IV_SKIN",substring_index(substring_index(qcdata,",",1),":",-1))=0,1,0))) as total_skin, ';

    $sql .=
      'sum(if(qcdata is null, 0, '.
      'if(strcmp("IV_ONE_LAYER",substring_index(substring_index(qcdata,",",1),":",-1))=0,1,0))) as total_one_layer, ';

    $sql .=
      'sum(if(qcdata is null, 0, '.
      'if(strcmp("IV_TWO_LAYERS",substring_index(substring_index(qcdata,",",1),":",-1))=0,1,0))) as total_two_layers, ';

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(cast(trim("}" from substring_index(substring_index(qcdata,",",4),":",-1)) as decimal(10,3))<%s,1,0))) as total_ratio_sub, ',$ratio_min);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(cast(trim("}" from substring_index(substring_index(qcdata,",",4),":",-1)) as decimal(10,3)) between %s and %s,1,0))) as total_ratio_par, ',$ratio_min,$ratio_max);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(cast(trim("}" from substring_index(qcdata,":",-1)) as decimal(10,3))>%s,1,0))) as total_ratio_sup, ',$ratio_max);

    $sql .= $this->get_main_query();

    $res = $db->get_all( $sql );
    if(false===$res || !is_array($res))
    {
      echo sprintf('error: failed query: %s', $db->get_last_error());
      echo $sql;
      die();
    }
    $this->data = $res;

    $this->page_explanation=array();
    $this->page_explanation[]='measurement over skin';
    $this->page_explanation[]='measurement over one layer';
    $this->page_explanation[]='measurement over two layers';
    $this->page_explanation[]=sprintf('subpar ratio: ratio < %s (mean - %s x SD)',$ratio_min,$this->standard_deviation_scale);
    $this->page_explanation[]=sprintf('par ratio: %s <= ratio <= %s',$ratio_min,$ratio_max);
    $this->page_explanation[]=sprintf('above par ratio: ratio > %s (mean + %s x SD)',$ratio_max,$this->standard_deviation_scale);
  }
}
