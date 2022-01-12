<?php
/**
 * patch.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\service\opal_view;
use cenozo\lib, cenozo\log, cosmos\util;

class patch extends \cenozo\service\patch
{
  /**
   * Extends parent method
   */
  protected function setup()
  {
    if( $this->get_argument( 'upload', false ) )
    {
      ini_set( 'memory_limit', '1G' );
      ini_set( 'upload_max_filesize', '128M' );
    }
    else
    {
      parent::setup();
    }
  }

  /**
   * Extends parent method
   */
  protected function execute()
  {
    if( $this->get_argument( 'upload', false ) )
    {
      // convert the raw file from a csv string to an associative array
      $setting_manager = lib::create( 'business\setting_manager' );
      $uid_regex = sprintf( '/%s/', $setting_manager->get_setting( 'general', 'uid_regex' ) );
      $data = array();
      foreach( preg_split( "/\r\n|\n|\r/", $this->get_file_as_raw() ) as $line )
      {
        $row = str_getcsv( $line );
        if( 2 == count( $row ) && preg_match( $uid_regex, $row[0] ) ) $data[$row[0]] = $row[1];
      }
      
      $this->get_leaf_record()->update_interview_list( $data );
    }
    else
    {
      parent::execute();
    }
  }
}
