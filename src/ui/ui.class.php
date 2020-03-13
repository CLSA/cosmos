<?php
/**
 * ui.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace cosmos\ui;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * Application extension to ui class
 */
class ui extends \cenozo\ui\ui
{
  /**
   * Extends the sparent method
   */
  protected function build_module_list()
  {
    parent::build_module_list();

    $module = $this->get_module( 'participant' );
    if( !is_null( $module ) ) $module->add_child( 'interview' );

    $module = $this->get_module( 'site' );
    if( !is_null( $module ) )
    {
      $module->add_child( 'technician' );
      $module->add_child( 'interview' );
    }

    $module = $this->get_module( 'technician' );
    if( !is_null( $module ) ) $module->add_child( 'stage' );

    $module = $this->get_module( 'interview' );
    if( !is_null( $module ) ) $module->add_child( 'stage' );

    $module = $this->get_module( 'platform' );
    if( !is_null( $module ) ) $module->add_child( 'stage_type' );

    $module = $this->get_module( 'stage_type' );
    if( !is_null( $module ) ) $module->add_child( 'indicator' );
  }

  /**
   * Extends the sparent method
   */
  protected function build_listitem_list()
  {
    parent::build_listitem_list();

    $this->add_listitem( 'Platforms', 'platform' );
    $this->add_listitem( 'Technicians', 'technician' );
    $this->add_listitem( 'Interviews', 'interview' );
    $this->add_listitem( 'Outliers', 'indicator' );

    $this->remove_listitem( 'Languages' );
  }

  /**
   * Extends the sparent method
   */
  protected function get_utility_items()
  {
    $list = parent::get_utility_items();

    // remove participant utilities
    unset( $list['Participant Export'] );
    unset( $list['Participant Multiedit'] );
    unset( $list['Participant Search'] );
    unset( $list['Tracing'] );

    return $list;
  }
}
