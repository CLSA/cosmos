<?php
/**
 * stage_type.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\database;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * stage_type: record
 */
class stage_type extends \cenozo\database\record
{
  /**
   * Returns the data table which contains this stage-type's data
   * @return string
   * @access public
   */
  public function get_data_table_name()
  {
    return sprintf(
      '%s_%s_%s_data',
      $this->get_study_phase()->code,
      $this->get_platform()->name,
      $this->name
    );
  }
}
