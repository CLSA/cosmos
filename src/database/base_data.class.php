<?php
/**
 * base_data.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\database;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * A base class for all records which represent data
 */
abstract class base_data extends \cenozo\database\record
{
  /**
   * TODO: document
   */
  public function set_data( $data )
  {
    foreach( $data as $key => $value )
      if( static::column_exists( $key ) )
        $this->$key = $value;
  }

  /**
   * TODO: document
   */
  public static function get_statistics( $column, $modifier = NULL )
  {
    if( is_null( $modifier ) ) $modifier = lib::create( 'database\modifier' );

    $select = lib::create( 'database\select' );
    $select->from( self::get_table_name() );
    $select->add_column( sprintf( 'MIN( %s )', $column ), 'minimum', false );
    $select->add_column( sprintf( 'MAX( %s )', $column ), 'maximum', false );
    $select->add_column( sprintf( 'AVG( %s )', $column ), 'average', false );
    $select->add_column( 'COUNT(*)', 'count', false );
    $modifier->WHERE( $column, '!=', NULL );

    $statistics = self::db()->get_row( sprintf(
      '%s %s',
      $select->get_sql(),
      $modifier->get_sql()
    ) );

    // return the middle value, sorting by the column
    $modifier->order( $column );
    $modifier->limit( 1 );
    $modifier->offset( floor( $statistics['count']/2 ) );

    $statistics['median'] = self::db()->get_one( sprintf(
      'SELECT %s FROM %s %s',
      $column,
      self::get_table_name(),
      $modifier->get_sql()
    ) );

    return $statistics;
  }
}
