<?php
/**
 * module.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\service\platform;
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

    $this->add_count_column( 'study_phase_count', 'opal_view', $select, $modifier );

    if( $select->has_column( 'stage_type_count' ) )
    {
      $join_sel = lib::create( 'database\select' );
      $join_sel->from( 'platform' );
      $join_sel->add_column( 'id', 'platform_id' );
      $join_sel->add_column( 'IF( stage_type.id IS NOT NULL, COUNT(*), 0 )', 'total', false );

      $join_mod = lib::create( 'database\modifier' );
      $join_mod->left_join( 'opal_view', 'platform.id', 'opal_view.platform_id' );
      $join_mod->left_join( 'stage_type', 'opal_view.id', 'stage_type.opal_view_id' );
      $join_mod->group( 'platform.id' );

      $modifier->join(
        sprintf( '( %s %s ) AS stage_type_join', $join_sel->get_sql(), $join_mod->get_sql() ),
        'platform.id',
        'stage_type_join.platform_id'
      );
      $select->add_column( 'stage_type_join.total', 'stage_type_count', false );
    }
  }
}
