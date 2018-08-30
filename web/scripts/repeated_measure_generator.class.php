<?php
require_once 'table_generator.class.php';

class repeated_measure_generator extends table_generator
{
  public function __construct($_stage, $_rank, $_begin_date = null, $_end_date = null)
  {
    parent::__construct($_stage, $_rank, $_begin_date, $_end_date);

    $this->indicator_keys=array(
      'total_deviation_sub','total_deviation_par','total_deviation_sup');
    $this->percent_keys = array(
      'total_trial_deviation','total_skip','total_unexplained_missing','total_contraindicated');

    $this->measurement_units = 'units';
    $this->trial_count = 2;
    $this->deviation_minimum = 0;
    $this->deviation_maximum = 0.1;
  }

  public function set_deviation_minimum( $_min )
  {
    if( 0 < $_min )
      $this->deviation_minimum = $_min;
  }

  public function set_deviation_maximum( $_max )
  {
    if( 0 < $_max )
      $this->deviation_maximum = $_max;
  }

  public function set_measurement_units( $_units )
  {
    if( '' != $_units )
      $this->measurement_units = $_units;
  }

  public function set_trial_count( $_ntrial )
  {
    if( 1 < $_ntrial )
      $this->trial_count = $_ntrial;
  }

  protected function build_data()
  {
    global $db;

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(cast(substring_index(substring_index(qcdata,",",1),":",-1) as decimal)<%s,1,0))) as total_deviation_par, ',
      $this->deviation_minimum);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(cast(substring_index(substring_index(qcdata,",",1),":",-1) as decimal) between %s and %s,1,0))) as total_deviation_sub, ',
      $this->deviation_minimum, $this->deviation_maximum);

    $sql .= sprintf(
      'sum(if(qcdata is null, 0, '.
      'if(cast(substring_index(substring_index(qcdata,",",1),":",-1) as decimal)>%s,1,0))) as total_deviation_sup, ',
      $this->deviation_maximum);

    $sql .=
      'sum(if(qcdata is null, 0, '.
      'if(cast(trim("}" from substring_index(substring_index(qcdata,",",2),":",-1)) as signed)!=2,1,0))) as total_trial_deviation, ';

    $sql .= $this->get_main_query();

    $res = $db->get_all( $sql );
    if(false===$res || !is_array($res))
    {
      echo sprintf('error: failed query: %s', $db->get_last_error());
      echo $sql;
      die();
    }
    $this->data = $res;

    $this->page_explanation = array();
    $this->page_explanation[]='Deviation = standard deviation of repeated measurements';
    $this->page_explanation[]=
      sprintf('deviation par: size < %s %s (measurement resolution)',
        $this->deviation_minimum, $this->measurement_units);
    $this->page_explanation[]=
      sprintf('deviation sub: %s <= size <= %s %s',
      $this->deviation_minimum,$this->deviation_maximum, $this->measurement_units);
    $this->page_explanation[]=
      sprintf('deviation sup: size > %s %s',
      $this->deviation_maximum, $this->measurement_units);
    $this->page_explanation[]=sprintf('Trial deviation signalled when more or less than %s measurements made', $this->trial_count);
  }

  public function build_table_data()
  {
    parent::build_table_data();

    $this->indicator_keys[]='total_trial_deviation';
  }

  private $deviation_minimum;

  private $deviation_maximum;

  private $measurement_units;

  private $trial_count;
}
