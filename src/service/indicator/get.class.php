<?php
/**
 * get.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\service\indicator;
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
      $db_indicator = $this->get_leaf_record();
      if( !is_null( $db_indicator ) ) $db_indicator->recalculate_boundaries();
    }
  }
}
