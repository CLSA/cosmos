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
    $opal_view_class_name = lib::get_class_name( 'database\opal_view' );

    $new_interviews = 0;
    $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'keep_updated', '=', true );
    $opal_view_list = $opal_view_class_name::select_objects( $modifier );
    foreach( $opal_view_list as $index => $db_opal_view )
    {
      log::info( sprintf(
        'Scanning Opal view "%s/%s" for new interviews [%d of %d]',
        $db_opal_view->get_project_name(),
        $db_opal_view->get_view_name(),
        $index+1,
        count( $opal_view_list )
      ) );
      
      $total = $db_opal_view->update_interview_list();
      log::info( sprintf( 'Finished, %d new interview%s added', $total, 1 == $total ? '' : 's' ) );
      
      $new_interviews += $total;
    }

    // update all outliers since we now have new data
    if( 0 < $new_interviews ) self::db()->execute( 'CALL update_outliers()' );

    return $new_interviews;
  }
}
