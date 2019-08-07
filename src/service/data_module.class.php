<?php
/**
 * module.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\service;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * Performs operations which effect how this module is used in a service
 */
class data_module extends \cenozo\service\site_restricted_module
{
  /**
   * Extend parent method
   */
  public function prepare_read( $select, $modifier )
  {
    parent::prepare_read( $select, $modifier );

    $all_sites = lib::create( 'business\session' )->get_role()->all_sites;

    $modifier->join( 'stage', $this->get_subject().'.stage_id', 'stage.id' );
    $modifier->join( 'interview', 'stage.interview_id', 'interview.id' );

    // restrict by site
    $db_restricted_site = $this->get_restricted_site();
    if( !is_null( $db_restricted_site ) )
      $modifier->where( 'interview.site_id', '=', $db_restricted_site->id );

    $plot = $this->get_argument( 'plot', false );
    if( $plot )
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
      $select->add_column( $plot, 'value' );
      $modifier->where( $plot, '!=', NULL );
      $modifier->order( 'interview.start_date' );
      $modifier->limit( 1000000 );
    }
    else
    {
      $modifier->join( 'participant', 'interview.participant_id', 'participant.id' );
      $modifier->join( 'study_phase', 'interview.study_phase_id', 'study_phase.id' );
    }
  }
}
