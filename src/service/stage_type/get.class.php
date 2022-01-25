<?php
/**
 * get.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\service\stage_type;
use cenozo\lib, cenozo\log;

class get extends \cenozo\service\get
{
  /** 
   * Extends parent method
   */
  protected function prepare()
  {
    parent::prepare();

    if( $this->get_argument( 'recalculate_boundaries', false ) )
    {
      $db_stage_type = $this->get_leaf_record();
      if( !is_null( $db_stage_type ) ) $db_stage_type->recalculate_boundaries();
    }
  }
}
