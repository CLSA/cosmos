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
class module extends \cenozo\service\site_restricted_module
{
  /**
   * Extend parent method
   */
  public function prepare_read( $select, $modifier )
  {
    parent::prepare_read( $select, $modifier );

    $modifier->join( 'stage_type', 'indicator.stage_type_id', 'stage_type.id' );
    $modifier->join( 'opal_view', 'stage_type.opal_view_id', 'opal_view.id' );
    $modifier->join( 'study_phase', 'opal_view.study_phase_id', 'study_phase.id' );
    $modifier->join( 'platform', 'opal_view.platform_id', 'platform.id' );

    if( $modifier->has_where( 'outlier.date' ) )
    {
      $modifier->join( 'outlier', 'indicator.id', 'outlier.indicator_id' );
      $modifier->group( 'indicator.id' );

      // restrict by site
      $db_restricted_site = $this->get_restricted_site();
      if( !is_null( $db_restricted_site ) )
        $modifier->where( 'outlier.site_id', '=', $db_restricted_site->id );
    }

    if( $select->has_column( 'outlier_low' ) || $select->has_column( 'outlier_high' ) )
    {
      $join_sel = lib::create( 'database\select' );
      $join_sel->from( 'indicator' );
      $join_sel->add_column( 'id', 'indicator_id' );
      if( $select->has_column( 'outlier_low' ) )
        $join_sel->add_column( 'SUM( IF( outlier.type = "low", 1, 0 ) )', 'outlier_low', false );
      if( $select->has_column( 'outlier_high' ) )
        $join_sel->add_column( 'SUM( IF( outlier.type = "high", 1, 0 ) )', 'outlier_high', false );

      $join_mod = lib::create( 'database\modifier' );
      $join_mod->left_join( 'outlier', 'indicator.id', 'outlier.indicator_id' );
      $join_mod->group( 'indicator.id' );

      // restrict by site
      $db_restricted_site = $this->get_restricted_site();
      if( !is_null( $db_restricted_site ) )
        $join_mod->where( 'outlier.site_id', '=', $db_restricted_site->id );

      $modifier->join(
        sprintf( '( %s %s ) AS outlier_join', $join_sel->get_sql(), $join_mod->get_sql() ),
        'indicator.id',
        'outlier_join.indicator_id'
      );

      if( $select->has_column( 'outlier_low' ) )
        $select->add_table_column( 'outlier_join', 'outlier_low' );
      if( $select->has_column( 'outlier_high' ) )
        $select->add_table_column( 'outlier_join', 'outlier_high' );
    }

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
        $select->add_constant( $db_indicator->get_median(), 'median', 'float' );
      }
    }
  }
}
