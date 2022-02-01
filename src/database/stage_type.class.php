<?php
/**
 * stage_type.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\database;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * stage_type: record
 */
class stage_type extends \cenozo\database\record
{
  /**
   * Overrides the parent save method.
   * @access public
   */
  public function save()
  {
    $update_outliers = $this->has_column_changed( 'duration_low' ) || $this->has_column_changed( 'duration_high' );

    parent::save();

    if( $update_outliers ) self::db()->execute( sprintf( 'CALL update_outlier_for_stage_type( %d )', $this->id ) );
  }

  /**
   * Recalculates the min and max duration for this stage type
   * 
   * The values are calculated by finding the outer fences (3 times the difference between the upper and lower quartiles)
   */
  public function recalculate_boundaries()
  {
    $modifier = lib::create( 'database\modifier' );
    $modifier->where( 'stage_type.id', '=', $this->id );
    static::recalculate_all_boundaries( $modifier );
  }

  /**
   * Recalculates the min and max durations for all stage types
   * 
   * The values are calculated by finding the outer fences (3 times the difference between the upper and lower quartiles)
   * @param database\modifier $modifier
   */
  public static function recalculate_all_boundaries( $modifier = NULL )
  {
    $stage_class_name = lib::get_class_name( 'database\stage' );

    $select = lib::create( 'database\select' );
    $select->from( 'stage_type' );
    $select->add_column( 'id' );
    $select->add_column( 'COUNT(*)', 'total', false );
    if( !is_object( $modifier ) ) $modifier = lib::create( 'database\modifier' );
    $modifier->join( 'stage', 'stage_type.id', 'stage.stage_type_id' );
    $modifier->where( 'duration', '!=', NULL );
    $modifier->group( 'stage_type.id' );

    foreach( static::select( $select, $modifier ) as $row )
    {
      $q2_index = ( $row['total']+1 )/2;
      $q1_index = ( $row['total']-floor( $q2_index )+1 )/2;
      $q3_index = ( $row['total']-floor( $q2_index )+1 )/2 + $q2_index;

      if( 0 < floor( $q1_index ) )
      {
        $db_stage_type = lib::create( 'database\stage_type', $row['id'] );

        // get Q1, Q2 and Q3 for all stage types (lower quartile, median, upper quartile)
        $q1_sel = lib::create( 'database\select' );
        $q1_sel->add_column( 'duration' );
        $q1_mod = lib::create( 'database\modifier' );
        $q1_mod->where( 'stage_type_id', '=', $db_stage_type->id );
        $q1_mod->where( 'duration', '!=', NULL );
        $q1_mod->order( 'duration' );
        $q1_mod->offset( floor( $q1_index )-1 );
        $q1_mod->limit( floor( $q1_index ) == $q1_index ? 1 : 2 );
        $q1_sum = 0;
        $points = $stage_class_name::select( $q1_sel, $q1_mod );
        foreach( $points as $r ) $q1_sum += $r['duration'];
        $q1 = $q1_sum / count( $points );

        $q2_sel = lib::create( 'database\select' );
        $q2_sel->add_column( 'duration' );
        $q2_mod = lib::create( 'database\modifier' );
        $q2_mod->where( 'stage_type_id', '=', $db_stage_type->id );
        $q2_mod->where( 'duration', '!=', NULL );
        $q2_mod->order( 'duration' );
        $q2_mod->offset( floor( $q2_index )-1 );
        $q2_mod->limit( floor( $q2_index ) == $q2_index ? 1 : 2 );
        $q2_sum = 0;
        $points = $stage_class_name::select( $q2_sel, $q2_mod );
        foreach( $points as $r ) $q2_sum += $r['duration'];
        $q2 = $q2_sum / count( $points );

        $q3_sel = lib::create( 'database\select' );
        $q3_sel->add_column( 'duration' );
        $q3_mod = lib::create( 'database\modifier' );
        $q3_mod->where( 'stage_type_id', '=', $db_stage_type->id );
        $q3_mod->where( 'duration', '!=', NULL );
        $q3_mod->order( 'duration' );
        $q3_mod->offset( floor( $q3_index )-1 );
        $q3_mod->limit( floor( $q3_index ) == $q3_index ? 1 : 2 );
        $q3_sum = 0;
        $points = $stage_class_name::select( $q3_sel, $q3_mod );
        foreach( $points as $r ) $q3_sum += $r['duration'];
        $q3 = $q3_sum / count( $points );

        $minmax_sel = lib::create( 'database\select' );
        $minmax_sel->add_column( 'MIN( duration )', 'min', false );
        $minmax_sel->add_column( 'MAX( duration )', 'max', false );
        $minmax_mod = lib::create( 'database\modifier' );
        $minmax_mod->where( 'stage_type_id', '=', $db_stage_type->id );
        $minmax_mod->where( 'duration', '!=', NULL );
        $minmax = $stage_class_name::select( $minmax_sel, $minmax_mod );
        $minmax = current( $minmax );

        $range = $q3 - $q1;

        // use the inner lower fence, or 0 as a minimum
        $lower_fence = $q1 - 1.5*$range;
        if( $lower_fence < $minmax['min'] ) $lower_fence = $minmax['min'];
        $lower_fence = floor( $lower_fence );

        // use the outer upper fence
        $upper_fence = $q3 + 3.0*$range;
        if( $upper_fence < $minmax['max'] ) $upper_fence = $minmax['max'];
        $upper_fence = floor( $upper_fence );

        $db_stage_type->duration_low = $lower_fence;
        $db_stage_type->duration_high = $upper_fence;
        $db_stage_type->save();
      }
    }
  }
}
