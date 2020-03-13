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

  /**
   * TODO: document
   */
  public function get_median()
  {
    $stage_table_name = lib::get_class_name( 'database\stage' );

    $stage_mod = lib::create( 'database\modifier' );
    $stage_mod->where( sprintf( 'JSON_EXISTS( data, "$.%s" )', $this->name ), '=', true );
    $count = $stage_table_name::count( $stage_mod );

    $stage_sel = lib::create( 'database\select' );
    $stage_sel->add_column( sprintf( 'JSON_VALUE( data, "$.%s" )', $this->name ), 'value', false );
    $stage_mod = lib::create( 'database\modifier' );
    $stage_mod->where( sprintf( 'JSON_EXISTS( data, "$.%s" )', $this->name ), '=', true );
    $stage_mod->order( sprintf( 'JSON_VALUE( data, "$.%s" )', $this->name ) );
    $stage_mod->limit( 1 );
    $stage_mod->offset( floor( $count/2 ) );
    $result = $stage_table_name::select( $stage_sel, $stage_mod );

    return current( $result )['value'];
  }
}
