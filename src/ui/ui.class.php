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

    $module = $this->get_module( 'indicator_issue' );
    if( !is_null( $module ) )
    {
      $module->add_child( 'indicator_issue_note' );
      $module->add_choose( 'stage' );
    }

    $module = $this->get_module( 'interview' );
    if( !is_null( $module ) ) $module->add_child( 'stage' );

    $module = $this->get_module( 'opal_view' );
    if( !is_null( $module ) )
    {
      $module->add_child( 'stage_type' );
      $module->add_action( 'upload', '/{identifier}' );
    }

    $module = $this->get_module( 'participant' );
    if( !is_null( $module ) ) $module->add_child( 'interview' );

    $module = $this->get_module( 'platform' );
    if( !is_null( $module ) ) $module->add_child( 'stage_type' );

    $module = $this->get_module( 'stage_issue' );
    if( !is_null( $module ) )
    {
      $module->add_child( 'stage_issue_note' );
      $module->add_choose( 'stage' );
    }

    $module = $this->get_module( 'stage_type' );
    if( !is_null( $module ) ) $module->add_child( 'indicator' );

    $module = $this->get_module( 'stage' );
    if( !is_null( $module ) )
    {
      $module->add_child( 'comment' );
      $module->add_child( 'adverse_event' );
    }

    $module = $this->get_module( 'technician' );
    if( !is_null( $module ) ) $module->add_child( 'stage' );

    $module = $this->get_module( 'site' );
    if( !is_null( $module ) )
    {
      $module->add_child( 'technician' );
      $module->add_child( 'interview' );
    }
  }

  /**
   * Extends the sparent method
   */
  protected function build_listitem_list()
  {
    parent::build_listitem_list();

    $this->add_listitem( 'Adverse Events', 'adverse_event' );
    $this->add_listitem( 'Platforms', 'platform' );
    $this->add_listitem( 'Interviews', 'interview' );
    $this->add_listitem( 'Opal Views', 'opal_view' );
    $this->add_listitem( 'Outliers', 'indicator' );
    $this->add_listitem( 'Indicator Issues', 'indicator_issue' );
    $this->add_listitem( 'Stage Issues', 'stage_issue' );
    $this->add_listitem( 'Stage Types', 'stage_type' );

    $this->remove_listitem( 'Languages' );

    // remove some lists from coordinators
    if( 'coordinator' == lib::create( 'business\session' )->get_role()->name )
    {
      $this->remove_listitem( 'Outliers' );
      $this->remove_listitem( 'Overviews' );
      $this->remove_listitem( 'Platforms' );
      $this->remove_listitem( 'Users' );
    }
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
