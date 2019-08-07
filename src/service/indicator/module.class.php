<?php
/**
 * module.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\service\indicator;
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

    $modifier->join( 'stage_type', 'indicator.stage_type_id', 'stage_type.id' );
    $modifier->join( 'study_phase', 'stage_type.study_phase_id', 'study_phase.id' );
    $modifier->join( 'platform', 'stage_type.platform_id', 'platform.id' );

    if( $select->has_column( 'min_date' ) || $select->has_column( 'max_date' ) )
    {
      $modifier->join( 'stage', 'stage_type.id', 'stage.stage_type_id' );
      $modifier->join( 'interview', 'stage.interview_id', 'interview.id' );
      if( $select->has_column( 'min_date' ) )
        $select->add_column( 'MIN( interview.start_date )', 'min_date', false );
      if( $select->has_column( 'max_date' ) )
        $select->add_column( 'MAX( interview.start_date )', 'max_date', false );
    }

    $db_indicator = $this->get_resource();
    if( !is_null( $db_indicator ) )
    {
      if( $select->has_column( 'median' ) )
      {
        $class_name = lib::get_class_name( sprintf(
          'database\%s',
          $db_indicator->get_stage_type()->get_data_table_name()
        ) );
        $select->add_constant( $class_name::get_statistics( $db_indicator->name )['median'], 'median', 'float' );
      }
    }
  }
}
