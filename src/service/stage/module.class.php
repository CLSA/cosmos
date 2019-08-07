<?php
/**
 * module.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\service\stage;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * Performs operations which effect how this module is used in a service
 */
class module extends \cenozo\service\site_restricted_module
{
  /**
   * Extend parent method
   */
  public function prepare_read( $select, $modifier )
  {
    parent::prepare_read( $select, $modifier );

    $all_sites = lib::create( 'business\session' )->get_role()->all_sites;

    $modifier->join( 'interview', 'stage.interview_id', 'interview.id' );

    // restrict by site
    $db_restricted_site = $this->get_restricted_site();
    if( !is_null( $db_restricted_site ) )
      $modifier->where( 'interview.site_id', '=', $db_restricted_site->id );

    if( $this->get_argument( 'plot', false ) )
    {
      $select->remove_column_by_column( '*' );

      if( $all_sites )
      {
        $select->add_table_column( 'site', 'name', 'category' );
        $modifier->join( 'site', 'interview.site_id', 'site.id' );
        $modifier->order( 'site.name' );
      }
      else
      {
        $select->add_table_column( 'technician', 'name', 'category' );
        $modifier->join( 'technician', 'stage.technician_id', 'technician.id' );
        $modifier->order( 'technician.name' );
      }

      $select->add_table_column( 'interview', 'start_date', 'date' );
      $select->add_column( 'duration', 'value' );
      $modifier->where( 'stage.duration', '!=', NULL );
      $modifier->order( 'interview.start_date' );
      $modifier->limit( 1000000 );
    }
    else
    {
      $modifier->join( 'stage_type', 'stage.stage_type_id', 'stage_type.id' );
      $modifier->join( 'study_phase', 'stage_type.study_phase_id', 'study_phase.id' );
      $modifier->join( 'platform', 'stage_type.platform_id', 'platform.id' );
      $modifier->join( 'participant', 'interview.participant_id', 'participant.id' );
      $modifier->left_join( 'technician', 'stage.technician_id', 'technician.id' );
    }
  }
}
