<?php
/**
 * opal_view.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\database;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * opal_view: record
 */
class opal_view extends \cenozo\database\record
{
  /**
   * Returns the view's project name
   * 
   * @return string
   */
  public function get_project_name()
  {
    return sprintf( 'cosmos_%s', $this->get_platform()->name );
  }

  /**
   * Returns the view's table name
   * 
   * @return string
   */
  public function get_view_name()
  {
    return sprintf( 'QC_%s_json', strtoupper( $this->get_study_phase()->code ) );
  }
}
