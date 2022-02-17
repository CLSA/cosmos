<?php
/**
 * indicator.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\database;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * indicator: record
 */
class indicator extends \cenozo\database\record
{
  /**
   * Overrides the parent save method.
   * @access public
   */
  public function save()
  {
    $update_outliers = $this->has_column_changed( 'minimum' ) || $this->has_column_changed( 'maximum' );

    parent::save();

    if( $update_outliers ) self::db()->execute( sprintf( 'CALL update_outlier_for_indicator( %d )', $this->id ) );
  }

  /**
   * Returns the database type that this indicator should be cast as
   * @return string
   */
  public function get_cast_type()
  {
    return 'float' == $this->type ? 'FLOAT' : 'INTEGER';
  }

  /**
   * Calculates this indicator's median value across all stages
   */
  public function get_median()
  {
    $stage_table_name = lib::get_class_name( 'database\stage' );

    if( in_array( $this->type, [ 'boolean', 'string' ] ) )
    {
      $median = NULL;
    }
    else
    {
      $json_params = sprintf( 'data, "$.%s"', $this->name );

      $stage_mod = lib::create( 'database\modifier' );
      $stage_mod->where( 'stage.stage_type_id', '=', $this->stage_type_id );
      $stage_mod->where( sprintf( 'JSON_EXISTS( %s )', $json_params ), '=', true );
      $count = $stage_table_name::count( $stage_mod );

      $stage_sel = lib::create( 'database\select' );
      $stage_sel->add_column( sprintf( 'CAST( JSON_VALUE( %s ) AS %s )', $json_params, $this->get_cast_type() ), 'value', false );
      $stage_mod = lib::create( 'database\modifier' );
      $stage_mod->where( 'stage.stage_type_id', '=', $this->stage_type_id );
      $stage_mod->where( sprintf( 'JSON_EXISTS( %s )', $json_params ), '=', true );
      $stage_mod->order( sprintf( 'CAST( JSON_VALUE( %s ) AS %s )', $json_params, $this->get_cast_type() ) );
      $stage_mod->limit( 1 );
      $stage_mod->offset( floor( $count/2 ) );
      $result = $stage_table_name::select( $stage_sel, $stage_mod );
      $median = current( $result )['value'];
    }

    return $median;
  }

  /**
   * Recalculates the min and max values for this indicator
   * 
   * The values are calculated by finding the outer fences (3 times the difference between the upper and lower quartiles)
   */
  public function recalculate_boundaries()
  {
    $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'indicator.id', '=', $this->id );
    static::recalculate_all_boundaries( $modifier );
  }

  /**
   * Recalculates the min and max values for all indicators
   * 
   * The values are calculated by finding the outer fences (3 times the difference between the upper and lower quartiles)
   * @param database\modifier $modifier
   */
  public static function recalculate_all_boundaries( $modifier = NULL )
  {
    $stage_class_name = lib::get_class_name( 'database\stage' );

    $select = lib::create( 'database\select' );
    $select->from( 'indicator' );
    $select->add_column( 'id' );
    $select->add_column( 'COUNT(*)', 'total', false );
    if( !is_object( $modifier ) ) $modifier = lib::create( 'database\modifier' );
    $modifier->join( 'stage_type', 'indicator.stage_type_id', 'stage_type.id' );
    $join_mod = lib::create( 'database\modifier' );
    $join_mod->where( 'stage_type.id', '=', 'stage.stage_type_id', false );
    $join_mod->where( 'JSON_EXISTS( data, CONCAT( "$.", indicator.name ) )', '=', true );
    $modifier->join_modifier( 'stage', $join_mod );
    $modifier->where( 'indicator.type', 'NOT IN', array( 'boolean', 'string' ) );
    $modifier->group( 'indicator.id' );

    foreach( static::select( $select, $modifier ) as $row )
    {
      $q2_index = ( $row['total']+1 )/2;
      $q1_index = ( $row['total']-floor( $q2_index )+1 )/2;
      $q3_index = ( $row['total']-floor( $q2_index )+1 )/2 + $q2_index;

      if( 0 < floor( $q1_index ) )
      {
        $db_indicator = lib::create( 'database\indicator', $row['id'] );
        $json_params = sprintf( 'data, "$.%s"', $db_indicator->name );

        // get Q1, Q2 and Q3 for all stage types (lower quartile, median, upper quartile)
        $q1_sel = lib::create( 'database\select' );
        $q1_sel->add_column(
          sprintf( 'CAST( JSON_VALUE( %s ) AS %s )', $json_params, $db_indicator->get_cast_type() ),
          'value',
          false
        );
        $q1_mod = lib::create( 'database\modifier' );
        $q1_mod->where( 'stage_type_id', '=', $db_indicator->stage_type_id );
        $q1_mod->where( sprintf( 'JSON_EXISTS( %s )', $json_params ), '=', true );
        $q1_mod->order( sprintf( 'CAST( JSON_VALUE( %s ) AS %s )', $json_params, $db_indicator->get_cast_type() ) );
        $q1_mod->offset( floor( $q1_index )-1 );
        $q1_mod->limit( floor( $q1_index ) == $q1_index ? 1 : 2 );
        $q1_sum = 0;
        $points = $stage_class_name::select( $q1_sel, $q1_mod );
        foreach( $points as $r ) $q1_sum += $r['value'];
        $q1 = $q1_sum / count( $points );

        $q2_sel = lib::create( 'database\select' );
        $q2_sel->add_column(
          sprintf( 'CAST( JSON_VALUE( %s ) AS %s )', $json_params, $db_indicator->get_cast_type() ),
          'value',
          false
        );
        $q2_mod = lib::create( 'database\modifier' );
        $q2_mod->where( 'stage_type_id', '=', $db_indicator->stage_type_id );
        $q2_mod->where( sprintf( 'JSON_EXISTS( %s )', $json_params ), '=', true );
        $q2_mod->order( sprintf( 'CAST( JSON_VALUE( %s ) AS %s )', $json_params, $db_indicator->get_cast_type() ) );
        $q2_mod->offset( floor( $q2_index )-1 );
        $q2_mod->limit( floor( $q2_index ) == $q2_index ? 1 : 2 );
        $q2_sum = 0;
        $points = $stage_class_name::select( $q2_sel, $q2_mod );
        foreach( $points as $r ) $q2_sum += $r['value'];
        $q2 = $q2_sum / count( $points );

        $q3_sel = lib::create( 'database\select' );
        $q3_sel->add_column(
          sprintf( 'CAST( JSON_VALUE( %s ) AS %s )', $json_params, $db_indicator->get_cast_type() ),
          'value',
          false
        );
        $q3_mod = lib::create( 'database\modifier' );
        $q3_mod->where( 'stage_type_id', '=', $db_indicator->stage_type_id );
        $q3_mod->where( sprintf( 'JSON_EXISTS( %s )', $json_params ), '=', true );
        $q3_mod->order( sprintf( 'CAST( JSON_VALUE( %s ) AS %s )', $json_params, $db_indicator->get_cast_type() ) );
        $q3_mod->offset( floor( $q3_index )-1 );
        $q3_mod->limit( floor( $q3_index ) == $q3_index ? 1 : 2 );
        $q3_sum = 0;
        $points = $stage_class_name::select( $q3_sel, $q3_mod );
        foreach( $points as $r ) $q3_sum += $r['value'];
        $q3 = $q3_sum / count( $points );

        $minmax_sel = lib::create( 'database\select' );
        $minmax_sel->add_column(
          sprintf( 'MIN( CAST( JSON_VALUE( %s ) AS %s ) )', $json_params, $db_indicator->get_cast_type() ),
          'min',
          false
        );
        $minmax_sel->add_column(
          sprintf( 'MAX( CAST( JSON_VALUE( %s ) AS %s ) )', $json_params, $db_indicator->get_cast_type() ),
          'max',
          false
        );
        $minmax_mod = lib::create( 'database\modifier' );
        $minmax_mod->where( 'stage_type_id', '=', $db_indicator->stage_type_id );
        $minmax_mod->where( sprintf( 'JSON_EXISTS( %s )', $json_params ), '=', true );
        $minmax = $stage_class_name::select( $minmax_sel, $minmax_mod );
        $minmax = current( $minmax );

        $range = $q3 - $q1;

        // use the outer lower fence, or 0 as a minimum
        $lower_fence = $q1 - 3.0*$range;
        if( $lower_fence < $minmax['min'] ) $lower_fence = $minmax['min'];
        if( 'integer' == $db_indicator->get_cast_type() ) $lower_fence = floor( $lower_fence );

        // use the outer upper fence
        $upper_fence = $q3 + 3.0*$range;
        if( $upper_fence > $minmax['max'] ) $upper_fence = $minmax['max'];
        if( 'integer' == $db_indicator->get_cast_type() ) $upper_fence = ceil( $upper_fence );

        $db_indicator->minimum = $lower_fence;
        $db_indicator->maximum = $upper_fence;
        $db_indicator->save();
      }
    }
  }
}
