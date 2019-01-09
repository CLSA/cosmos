<?php
require_once 'table_generator.class.php';

class refusal_generator extends table_generator
{
  public function __construct($_stage, $_rank, $_begin_date = null, $_end_date = null)
  {
    parent::__construct($_stage, $_rank, $_begin_date, $_end_date);

    $this->indicator_keys = array();
    $this->module_threshold = 90;
  }

  public function set_module_threshold($_t)
  {
    $this->module_threshold = $_t;
  }

  protected function build_data()
  {
    global $db;

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    // get the names of the modules
    $sql_module = sprintf(
      'select qcdata '.
      'from stage s '.
      'join interview i on i.id=s.interview_id '.
      'where name="%s" '.
      'and rank=%d '.
      'and qcdata is not null '.
      'limit 1', $this->name, $this->rank);
    $res = $db->get_one( $sql_module );
    if(false===$res) {
      var_dump($sql_module);
      die();
    }
    preg_match_all('/,(.*?):/',$res,$module_name_list);
    $module_name_list = array_pop($module_name_list);

    for( $i = 0; $i < count($module_name_list); $i++ )
    {
      $refusal_key = 'total_refusal_'.$module_name_list[$i];
      $this->indicator_keys[] = $refusal_key;

      $sql .= sprintf(
        'sum(if(qcdata is null, 0, '.
        'if(cast(substring_index(substring_index(qcdata,",",%d),":",-1) as decimal(6,3))>%s,1,0))) as total_refusal_%s, ',
        $i+2,
        $this->module_threshold,
        $module_name_list[$i]);
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
  }

  private $module_threshold;
}
