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

    $this->add_count_column( 'stage_count', 'stage', $select, $modifier );

    $db_stage_type = $this->get_resource();
    if( !is_null( $db_stage_type ) )
    {
      if( $select->has_column( 'contraindicated' ) ||
          $select->has_column( 'missing' ) ||
          $select->has_column( 'skip' ) )
      {
        $modifier->join( 'stage', 'stage_type.id', 'stage.stage_type_id' );
        if( $select->has_column( 'contraindicated' ) )
          $select->add_column( 'COUNT( contraindicated )', 'contraindicated', false );
        if( $select->has_column( 'missing' ) )
          $select->add_column( 'COUNT( missing )', 'missing', false );
        if( $select->has_column( 'skip' ) )
          $select->add_column( 'COUNT( skip )', 'skip', false );
      }
    }
  }
}
