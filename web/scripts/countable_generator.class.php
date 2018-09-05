<?php
require_once 'table_generator.class.php';

class countable_generator extends table_generator
{
  public function __construct($_stage, $_rank, $_begin_date = null, $_end_date = null)
  {
    parent::__construct($_stage, $_rank, $_begin_date, $_end_date);

    $this->countable_label = 'trial';
    $this->indicator_keys = array('total_trial_sub','total_trial_par','total_trial_sup');
    $this->par_levels = array('sub'=>'subpar','par'=>'on par','sup'=>'above par');
    $this->par_keys = array_keys($this->par_levels);
    $this->countable_target = 3;
  }

  public function set_countable_target( $_target )
  {
    if( 0 < $_target )
      $this->countable_target = $_target;
  }

  public function set_countable_label( $_label )
  {
    if( $this->countable_label == $_label ) return;
    $this->indicator_keys = str_replace($this->countable_label, $_label, $this->indicator_keys);
    $this->countable_label = $_label;
  }

  public function set_par_keys( $_levels )
  {
    if(!is_array($_levels)) return;

    $keys = array_intersect(array_keys($this->par_levels), $_levels);
    if(0 < count($keys))
    {
      $this->par_keys = $keys;
    }
  }

  protected function build_data()
  {
    global $db;

    $tmp = array();
    foreach($this->indicator_keys as $item)
    {
      foreach($this->par_keys as $key)
      {
        if(0===substr_compare( $item, $key, -3 ))
        {
          $tmp[] = $item;
          break;
        }
      }
    }
    if(0 < count($tmp))
      $this->indicator_keys = $tmp;

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    if(in_array('sub',$this->par_keys))
    {
      $sql .= sprintf('sum(if(qcdata is null, 0, if(cast(trim("}" from substring_index(qcdata,":",-1)) as signed)<%s,1,0))) as total_%s_sub, ',
      $this->countable_target, $this->countable_label);
    }
    if(in_array('par',$this->par_keys))
    {
      $sql .= sprintf('sum(if(qcdata is null, 0, if(cast(trim("}" from substring_index(qcdata,":",-1)) as signed)=%s,1,0))) as total_%s_par, ',
        $this->countable_target, $this->countable_label);
    }
    if(in_array('sup',$this->par_keys))
    {
      $sql .= sprintf('sum(if(qcdata is null, 0, if(cast(trim("}" from substring_index(qcdata,":",-1)) as signed)>%s,1,0))) as total_%s_sup, ',
        $this->countable_target, $this->countable_label);
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

    $this->page_explanation=array();
    if(in_array('sub',$this->par_keys))
      $this->page_explanation[]=sprintf('%s subpar: < %s', $this->countable_label, $this->countable_target);
    if(in_array('par',$this->par_keys))
      $this->page_explanation[]=sprintf('%s on par: = %s', $this->countable_label, $this->countable_target);
      if(in_array('sup',$this->par_keys))
    $this->page_explanation[]=sprintf('%s above par: > %s', $this->countable_label, $this->countable_target);
  }

  protected $countable_target;

  protected $countable_label;

  protected $par_levels;

  protected $par_keys;
}
