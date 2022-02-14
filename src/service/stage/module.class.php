<?php
/**
 * module.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\service\stage;
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
    $indicator_class_name = lib::get_class_name( 'database\indicator' );

    parent::prepare_read( $select, $modifier );

    $all_sites = lib::create( 'business\session' )->get_role()->all_sites;
    $modifier->join( 'interview', 'stage.interview_id', 'interview.id' );
    $modifier->join( 'participant', 'interview.participant_id', 'participant.id' );

    // restrict by site
    $db_restricted_site = $this->get_restricted_site();
    if( !is_null( $db_restricted_site ) )
      $modifier->where( 'interview.site_id', '=', $db_restricted_site->id );

    $plot = $this->get_argument( 'plot', false );
    if( $plot )
    {
      $select->remove_column_by_column( '*' );

      if( $all_sites )
      {
        $select->add_table_column( 'site', 'name', 'category' );
        $modifier->join( 'site', 'interview.site_id', 'site.id' );
        $modifier->order( 'site.name' );
      }
      else
      {
        $select->add_table_column( 'technician', 'name', 'category' );
        $modifier->join( 'technician', 'stage.technician_id', 'technician.id' );
        $modifier->order( 'technician.name' );
      }

      $select->add_table_column( 'interview', 'start_date', 'date' );
      if( 'duration' == $plot )
      {
        $select->add_column( 'duration', 'value' );
        $modifier->where( 'stage.duration', '!=', NULL );
      }
      else
      {
        $db_indicator = $indicator_class_name::get_unique_record(
          array( 'stage_type_id', 'name' ),
          array( $this->get_parent_resource()->id, $plot )
        );

        $json_params = sprintf( 'data, "$.%s"', $plot );
        $select->add_column(
          sprintf( 'CAST( JSON_VALUE( %s ) AS %s )', $json_params, $db_indicator->get_cast_type() ),
          'value',
          false
        );
        $modifier->where( sprintf( 'JSON_EXISTS( %s )', $json_params ), '=', true );
      }

      $modifier->order( 'interview.start_date' );
      $modifier->limit( 1000000 );
    }
    else
    {
      $modifier->join( 'stage_type', 'stage.stage_type_id', 'stage_type.id' );
      $modifier->join( 'opal_view', 'stage_type.opal_view_id', 'opal_view.id' );
      $modifier->join( 'study_phase', 'opal_view.study_phase_id', 'study_phase.id' );
      $modifier->join( 'platform', 'opal_view.platform_id', 'platform.id' );
      $modifier->join( 'participant', 'interview.participant_id', 'participant.id' );
      $modifier->left_join( 'technician', 'stage.technician_id', 'technician.id' );

      if( $select->has_column( 'comment_list' ) )
      {
        $this->add_list_column(
          'comment_list',
          'comment',
          'CONCAT_WS( ": ", comment.type, comment.note )',
          $select,
          $modifier,
          NULL,
          NULL,
          'rank',
          '; ',
          false
        );
      }
    }
  }
}
