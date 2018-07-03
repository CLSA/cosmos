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

  public function set_standard_deviation_scale($_stdev)
  {
    $this->standard_deviation_scale = $_stdev;
  }

  protected function build_data()
  {
    global $db;

    // build the main query

    $sql = sprintf(
      'select avg( '.
      '  if( qcdata is null, null, '.
      '      trim("}" from '.
      '        substring_index( '.
      '          substring_index( '.
      '            qcdata, ",", 4), ":", -1 ) ) ) ) as r_avg '.
      'from interview i '.
      'join stage s on i.id=s.interview_id '.
      'where rank=%d '.
      'and s.name="%s"', $this->rank, $this->name);

    $avg = $db->get_one( $sql );
    if( false === $avg )
    {
      echo sprintf('failed to get average hips to waist ratio: %s', $db->get_last_error() );
      echo $sql;
      die();
    }

    $sql = sprintf(
      'select stddev( '.
      '  if( qcdata is null, null, '.
      '      trim("}" from '.
      '        substring_index( '.
      '          substring_index( '.
      '            qcdata, ",", 4), ":", -1 ) ) ) ) as r_avg '.
      'from interview i '.
      'join stage s on i.id=s.interview_id '.
      'where rank=%d '.
      'and s.name="%s"', $this->rank, $this->name);

    $std = $db->get_one( $sql );
    if( false === $std )
    {
      echo sprintf('failed to get stddev hips to waist ratio: %s', $db->get_last_error() );
      echo $sql;
      die();
    }

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
      'if(trim("}" from substring_index(substring_index(qcdata,",",4),":",-1))<%s,1,0))) as total_ratio_sub, ',$ratio_min);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(trim("}" from substring_index(substring_index(qcdata,",",4),":",-1)) between %s and %s,1,0))) as total_ratio_par, ',$ratio_min,$ratio_max);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(trim("}" from substring_index(qcdata,":",-1))>%s,1,0))) as total_ratio_sup, ',$ratio_max);

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
    $this->page_explanation[]=sprintf('hips to waist ratio sub: ratio < %s (mean - %s x SD)',$ratio_min,$this->standard_deviation_scale);
    $this->page_explanation[]=sprintf('hips to waist ratio par: %s <= ratio <= %s',$ratio_min,$ratio_max);
    $this->page_explanation[]=sprintf('hips to waist ratio sup: ratio > %s (mean + %s x SD)',$ratio_max,$this->standard_deviation_scale);
  }

  private $standard_deviation_scale;

}
