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
class module extends \cenozo\service\module
{
  /**
   * Extend parent method
   */
  public function prepare_read( $select, $modifier )
  {
    parent::prepare_read( $select, $modifier );

    $modifier->join( 'stage_type', 'stage.stage_type_id', 'stage_type.id' );
    $modifier->join( 'study_phase', 'stage_type.study_phase_id', 'study_phase.id' );
    $modifier->join( 'platform', 'stage_type.platform_id', 'platform.id' );
    $modifier->join( 'interview', 'stage.interview_id', 'interview.id' );
    $modifier->join( 'participant', 'interview.participant_id', 'participant.id' );
    $modifier->left_join( 'technician', 'stage.technician_id', 'technician.id' );
  }
}
