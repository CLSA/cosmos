<?php
/**
 * module.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\service\stage_type;
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

    $db_stage_type = $this->get_resource();
    if( !is_null( $db_stage_type ) )
    {
      if( $select->has_column( 'min_date' ) ||
          $select->has_column( 'max_date' ) ||
          $select->has_column( 'contraindicated' ) ||
          $select->has_column( 'missing' ) ||
          $select->has_column( 'skip' ) ||
          $select->has_column( 'interviewer_decision' ) ||
          $select->has_column( 'interviewer_lack_of_time' ) ||
          $select->has_column( 'interviewer_refused_health_safety' ) ||
          $select->has_column( 'participant_decision' ) ||
          $select->has_column( 'participant_lack_of_time' ) ||
          $select->has_column( 'participant_refused_health_safety' ) ||
          $select->has_column( 'participant_refused_other' ) ||
          $select->has_column( 'participant_unable_to_complete' ) ||
          $select->has_column( 'see_comment' ) ||
          $select->has_column( 'technical_issue' ) ||
          $select->has_column( 'technical_problem' ) ||
          $select->has_column( 'other' ) )
      {
        $modifier->join( 'stage', 'stage_type.id', 'stage.stage_type_id' );
        $modifier->join( 'interview', 'stage.interview_id', 'interview.id' );

        if( $select->has_column( 'min_date' ) )
          $select->add_column( 'MIN( interview.start_date )', 'min_date', false );
        if( $select->has_column( 'max_date' ) )
          $select->add_column( 'MAX( interview.start_date )', 'max_date', false );
        if( $select->has_column( 'contraindicated' ) )
          $select->add_column( 'SUM( IF( contraindicated, 1, 0 ) )', 'contraindicated', false );
        if( $select->has_column( 'missing' ) )
          $select->add_column( 'SUM( IF( missing, 1, 0 ) )', 'missing', false );
        if( $select->has_column( 'skip' ) )
          $select->add_column( 'SUM( IF( skip IS NOT NULL, 1, 0 ) )', 'skip', false );
        if( $select->has_column( 'interviewer_decision' ) )
          $select->add_column( 'SUM( IF( skip = "InterviewerDecision", 1, 0 ) )', 'interviewer_decision', false );
        if( $select->has_column( 'interviewer_lack_of_time' ) )
          $select->add_column( 'SUM( IF( skip = "InterviewerLackOfTime", 1, 0 ) )', 'interviewer_lack_of_time', false );
        if( $select->has_column( 'interviewer_refused_health_safety' ) )
          $select->add_column( 'SUM( IF( skip = "InterviewerRefusedHealthSafety", 1, 0 ) )', 'interviewer_refused_health_safety', false );
        if( $select->has_column( 'participant_decision' ) )
          $select->add_column( 'SUM( IF( skip = "ParticipantDecision", 1, 0 ) )', 'participant_decision', false );
        if( $select->has_column( 'participant_lack_of_time' ) )
          $select->add_column( 'SUM( IF( skip = "ParticipantLackOfTime", 1, 0 ) )', 'participant_lack_of_time', false );
        if( $select->has_column( 'participant_refused_health_safety' ) )
          $select->add_column( 'SUM( IF( skip = "ParticipantRefusedHealthSafety", 1, 0 ) )', 'participant_refused_health_safety', false );
        if( $select->has_column( 'participant_refused_other' ) )
          $select->add_column( 'SUM( IF( skip = "ParticipantRefusedOther", 1, 0 ) )', 'participant_refused_other', false );
        if( $select->has_column( 'participant_unable_to_complete' ) )
          $select->add_column( 'SUM( IF( skip = "ParticipantUnableToComplete", 1, 0 ) )', 'participant_unable_to_complete', false );
        if( $select->has_column( 'see_comment' ) )
          $select->add_column( 'SUM( IF( skip = "SeeComment", 1, 0 ) )', 'see_comment', false );
        if( $select->has_column( 'technical_issue' ) )
          $select->add_column( 'SUM( IF( skip = "TechnicalIssue", 1, 0 ) )', 'technical_issue', false );
        if( $select->has_column( 'technical_problem' ) )
          $select->add_column( 'SUM( IF( skip = "TechnicalProblem", 1, 0 ) )', 'technical_problem', false );
        if( $select->has_column( 'other' ) )
          $select->add_column( 'SUM( IF( skip = "Other", 1, 0 ) )', 'other', false );
      }
    }
  }
}
