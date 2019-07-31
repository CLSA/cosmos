<?php
/**
 * module.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\service;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * Performs operations which effect how this module is used in a service
 */
class data_module extends \cenozo\service\module
{
  /**
   * Extend parent method
   */
  public function prepare_read( $select, $modifier )
  {
    parent::prepare_read( $select, $modifier );

    $plot = $this->get_argument( 'plot', false );
    if( $plot )
    {
      // use the median to determine the bins to put data into
      $class_name = lib::get_class_name( 'database\\'.$this->get_subject() );
      $stats = $class_name::get_statistics( $plot );

      $min_spread = $stats['median'] - $stats['minimum'];
      $max_spread = $stats['maximum'] - $stats['median'];
      if( $min_spread < $max_spread )
      {
        $min = $stats['minimum'];
        $max = $min + 3*$stats['median'];
        if( $max > $stats['maximum'] ) $max = $stats['maximum'];
        $boundary = $max;
      }
      else
      {
        $max = $stats['maximum'];
        $min = $max - 3*$stats['median'];
        if( $min < $stats['minimum'] ) $min = $stats['minimum'];
        $boundary = $min;
      }

      $bin = ( $max - $min ) / 50;

      $column = $plot;
      if( 0 < $bin )
      { // only divide up the values if we have bins to divide them into
        $column = sprintf(
          $min_spread < $max_spread ? 'CEIL( IF( %s>%f, %f+1, %s)/%f )*%f' : 'CEIL( IF( %s<%f, %f-1, %s)/%f )*%f',
          $plot,
          $boundary,
          $boundary,
          $plot,
          $bin,
          $bin
        );
      }

      $select->remove_column_by_column( '*' );
      $select->add_column( $column, 'value', false, 'integer' );
      $select->add_column( 'COUNT(*)', 'count', false, 'integer' );
      $modifier->where( $plot, '!=', NULL );
      $modifier->group( $column );
      $modifier->order( $plot );
      $modifier->limit( 1000000 );
    }
    else
    {
      $modifier->join( 'stage', $this->get_subject().'.stage_id', 'stage.id' );
      $modifier->join( 'interview', 'stage.interview_id', 'interview.id' );
      $modifier->join( 'participant', 'interview.participant_id', 'participant.id' );
      $modifier->join( 'study_phase', 'interview.study_phase_id', 'study_phase.id' );
    }
  }
}
