<?php
/**
 * module.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\service\adverse_event;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * Performs operations which effect how this module is used in a service
 */
class module extends \cenozo\service\site_restricted_module
{
  /**
   * Extend parent method
   */
  public function validate()
  {
    parent::validate();

    if( 300 > $this->get_status()->get_code() )
    {   
      $db_adverse_event = $this->get_resource();
      if( !is_null( $db_adverse_event ) ) 
      {   
        // restrict by site
        $db_restrict_site = $this->get_restricted_site();
        if( !is_null( $db_restrict_site ) ) 
        {
          if( $db_adverse_event->get_stage()->get_technician()->site_id != $db_restrict_site->id )
            $this->get_status()->set_code( 403 );
        }
      }   
    }   
  }

  /**
   * Extend parent method
   */
  public function prepare_read( $select, $modifier )
  {
    parent::prepare_read( $select, $modifier );

    $modifier->join( 'stage', 'adverse_event.stage_id', 'stage.id' );
    $modifier->join( 'interview', 'stage.interview_id', 'interview.id' );
    $modifier->join( 'participant', 'interview.participant_id', 'participant.id' );
    $modifier->join( 'study_phase', 'interview.study_phase_id', 'study_phase.id' );
    $modifier->join( 'platform', 'interview.platform_id', 'platform.id' );
    $modifier->join( 'technician', 'stage.technician_id', 'technician.id' );
    $modifier->join( 'site', 'technician.site_id', 'site.id' );
    $modifier->join( 'stage_type', 'stage.stage_type_id', 'stage_type.id' );
  }
}
