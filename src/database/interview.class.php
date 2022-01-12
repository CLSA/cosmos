<?php
/**
 * interview.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\database;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * interview: record
 */
class interview extends \cenozo\database\record
{
  /**
   * Adds any data files from Opal which don't already exist
   * 
   * @access public
   */
  public static function update_interview_list()
  {
    $max_new_interviews = 0;
    $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'keep_updated', '=', true );
    $opal_view_list = $opal_view_class_name::select_objects( $modifier );
    foreach( $opal_view_list as $index => $db_opal_view )
    {
      $project_name = $db_opal_view->get_project_name();
      $view_name = $db_opal_view->get_view_name();
      log::info( sprintf( 'Scanning %s/%s [%d of %d]', $project_name, $view_name, $index+1, count( $opal_view_list ) ) );

      $new_interviews = $db_opal_view->update_inerview_list();
      if( $new_interviews > $max_new_interviews ) $max_new_interviews = $new_interviews;
    }

    // update all outliers since we now have new data
    if( 0 < $max_new_interviews ) self::db()->execute( 'CALL update_outliers()' );

    return $max_new_interviews;
  }
}
