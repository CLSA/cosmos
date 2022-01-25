<?php
/**
 * query.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\service\stage_type;
use cenozo\lib, cenozo\log;

class query extends \cenozo\service\query
{
  /** 
   * Extends parent method
   */
  protected function prepare()
  {
    parent::prepare();

    if( $this->get_argument( 'recalculate_boundaries', false ) )
    {
      $stage_type_class_name = lib::get_class_name( 'database\stage_type' );
      $indicator_class_name = lib::get_class_name( 'database\indicator' );
      $stage_type_class_name::recalculate_all_boundaries();
      $indicator_class_name::recalculate_all_boundaries();
    }
  }
}
