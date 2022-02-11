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

    $modifier->join( 'opal_view', 'stage_type.opal_view_id', 'opal_view.id' );
    $modifier->join( 'study_phase', 'opal_view.study_phase_id', 'study_phase.id' );
    $modifier->join( 'platform', 'opal_view.platform_id', 'platform.id' );

    $db_stage_type = $this->get_resource();
    if( !is_null( $db_stage_type ) )
    {
      if( $select->has_column( 'median' ) ) $select->add_constant( $db_stage_type->get_median(), 'median', 'float' );

      if( $select->has_column( 'min_date' ) ||
          $select->has_column( 'max_date' ) ||
          $select->has_column( 'contraindicated_count' ) ||
          $select->has_column( 'missing_count' ) )
      {
        $modifier->join( 'stage', 'stage_type.id', 'stage.stage_type_id' );
        $modifier->join( 'interview', 'stage.interview_id', 'interview.id' );

        if( $select->has_column( 'min_date' ) )
          $select->add_column( 'MIN( interview.start_date )', 'min_date', false );
        if( $select->has_column( 'max_date' ) )
          $select->add_column( 'MAX( interview.start_date )', 'max_date', false );
        if( $select->has_column( 'contraindicated_count' ) )
          $select->add_column( 'SUM( IF( contraindicated, 1, 0 ) )', 'contraindicated_count', false );
        if( $select->has_column( 'missing_count' ) )
          $select->add_column( 'SUM( IF( missing, 1, 0 ) )', 'missing_count', false );
      }
    }
  }
}
