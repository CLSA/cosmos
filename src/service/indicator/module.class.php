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
    $indicator_table_name = lib::get_class_name( 'database\indicator' );

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

    if( $select->has_column( 'outlier_low' ) )
    {
      $indicator_table_name::db()->execute(
        'CREATE TEMPORARY TABLE temp_outlier_low '.
        'SELECT indicator.id AS indicator_id, IF( outlier.id IS NULL, 0, COUNT(*) ) AS total '.
        'FROM indicator '.
        'LEFT JOIN outlier ON indicator.id = outlier.indicator_id AND outlier.type = "low" '.
        'GROUP BY indicator.id'
      );
      $indicator_table_name::db()->execute( 'ALTER TABLE temp_outlier_low ADD INDEX fk_indicator_id( indicator_id )' );

      $modifier->join( 'temp_outlier_low', 'indicator.id', 'temp_outlier_low.indicator_id' );
      $select->add_table_column( 'temp_outlier_low', 'total', 'outlier_low' );
    }

    if( $select->has_column( 'outlier_high' ) )
    {
      $indicator_table_name::db()->execute(
        'CREATE TEMPORARY TABLE temp_outlier_high '.
        'SELECT indicator.id AS indicator_id, IF( outlier.id IS NULL, 0, COUNT(*) ) AS total '.
        'FROM indicator '.
        'LEFT JOIN outlier ON indicator.id = outlier.indicator_id AND outlier.type = "high" '.
        'GROUP BY indicator.id'
      );
      $indicator_table_name::db()->execute( 'ALTER TABLE temp_outlier_high ADD INDEX fk_indicator_id( indicator_id )' );

      $modifier->join( 'temp_outlier_high', 'indicator.id', 'temp_outlier_high.indicator_id' );
      $select->add_table_column( 'temp_outlier_high', 'total', 'outlier_high' );
    }

    if( $select->has_column( 'min_date' ) || $select->has_column( 'max_date' ) )
    {
      $modifier->join( 'stage', 'stage_type.id', 'stage.stage_type_id' );
      $modifier->join( 'interview', 'stage.interview_id', 'interview.id' );
      if( $select->has_column( 'min_date' ) )
        $select->add_column( 'MIN( interview.start_date )', 'min_date', false );
      if( $select->has_column( 'max_date' ) )
        $select->add_column( 'MAX( interview.start_date )', 'max_date', false );
      $modifier->group( 'indicator.id' );
    }

    $db_indicator = $this->get_resource();
    if( !is_null( $db_indicator ) )
    {
      if( $select->has_column( 'median' ) ) $select->add_constant( $db_indicator->get_median(), 'median', 'float' );
    }
  }
}
