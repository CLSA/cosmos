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
      if( $select->has_column( 'contraindicated' ) ||
          $select->has_column( 'missing' ) ||
          $select->has_column( 'skip' ) ||
          $select->has_column( 'min_date' ) ||
          $select->has_column( 'max_date' ) )
      {
        $modifier->join( 'stage', 'stage_type.id', 'stage.stage_type_id' );
        $modifier->join( 'interview', 'stage.interview_id', 'interview.id' );
        if( $select->has_column( 'contraindicated' ) )
          $select->add_column( 'SUM( IF( contraindicated, 1, 0 ) )', 'contraindicated', false );
        if( $select->has_column( 'missing' ) )
          $select->add_column( 'SUM( IF( missing, 1, 0 ) )', 'missing', false );
        if( $select->has_column( 'skip' ) )
          $select->add_column( 'SUM( IF( skip IS NOT NULL, 1, 0 ) )', 'skip', false );
        if( $select->has_column( 'min_date' ) )
          $select->add_column( 'MIN( start_date )', 'min_date', false );
        if( $select->has_column( 'max_date' ) )
          $select->add_column( 'MAX( start_date )', 'max_date', false );
        $modifier->where( 'stage.duration', '!=', NULL );
      }
    }
  }
}
