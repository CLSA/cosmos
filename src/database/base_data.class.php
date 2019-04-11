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
  public function set_data( $data )
  {
    foreach( $data as $key => $value )
      if( static::column_exists( $key ) )
        $this->$key = $value;
  }
}
