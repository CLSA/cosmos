<?php
/**
 * indicator.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\database;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * indicator: record
 */
class indicator extends \cenozo\database\record
{
  /**
   * Overrides the parent save method.
   * @access public
   */
  public function save()
  {
    $update_outliers = $this->has_column_changed( 'minimum' ) || $this->has_column_changed( 'maximum' );

    parent::save();

    if( $update_outliers ) self::db()->execute( sprintf( 'CALL update_outlier( %d )', $this->id ) );
  }
}
