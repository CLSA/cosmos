<?php
/**
 * overview.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\business\overview;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * overview: site
 */
class site extends \cenozo\business\overview\base_overview
{
  /**
   * Implements abstract method
   */
  protected function build()
  {
    $opal_view_class_name = lib::get_class_name( 'database\opal_view' );
    $interview_class_name = lib::get_class_name( 'database\interview' );
    $stage_class_name = lib::get_class_name( 'database\stage' );
    $indicator_class_name = lib::get_class_name( 'database\indicator' );
    $adverse_event_class_name = lib::get_class_name( 'database\adverse_event' );
    $outlier_class_name = lib::get_class_name( 'database\outlier' );
    $indicator_issue_class_name = lib::get_class_name( 'database\indicator_issue' );
    $stage_issue_class_name = lib::get_class_name( 'database\stage_issue' );

    $session = lib::create( 'business\session' );
    $db_application = $session->get_application();
    $db_role = $session->get_role();
    $db_site = $session->get_site();

    // get all of the data we need directly (for performance)
    $interview_sel = lib::create( 'database\select' );
    $interview_sel->from( 'interview' );
    $interview_sel->add_column( 'site_id' );
    $interview_sel->add_column( 'study_phase_id' );
    $interview_sel->add_column( 'platform_id' );
    $interview_sel->add_column( 'MIN( start_date )', 'min_date', false );
    $interview_sel->add_column( 'MAX( start_date )', 'max_date', false );
    $interview_sel->add_column( 'COUNT(*)', 'total', false );
    $interview_mod = lib::create( 'database\modifier' );
    $join_mod = lib::create( 'database\modifier' );
    $join_mod->where( 'interview.study_phase_id', '=', 'opal_view.study_phase_id', false );
    $join_mod->where( 'interview.platform_id', '=', 'opal_view.platform_id', false );
    $interview_mod->join_modifier( 'opal_view', $join_mod );
    $interview_mod->where( 'opal_view.keep_updated', '=', true );
    $interview_mod->group( 'site_id' );
    $interview_mod->group( 'study_phase_id' );
    $interview_mod->group( 'platform_id' );
    
    $interview_data = array();
    foreach( $interview_class_name::select( $interview_sel, $interview_mod ) as $row )
    {
      $data_index = sprintf( '%d-%d-%d', $row['site_id'], $row['study_phase_id'], $row['platform_id'] );
      $interview_data[$data_index] = array(
        'min_date' => $row['min_date'],
        'max_date' => $row['max_date'],
        'total' => $row['total']
      );

      // now find the median value
      $interview_sel = lib::create( 'database\select' );
      $interview_sel->from( 'interview' );
      $interview_sel->add_column( 'duration' );
      $interview_mod = lib::create( 'database\modifier' );
      $interview_mod->where( 'site_id', '=', $row['site_id'] );
      $interview_mod->where( 'interview.study_phase_id', '=', $row['study_phase_id'] );
      $interview_mod->where( 'interview.platform_id', '=', $row['platform_id'] );
      $interview_mod->order( 'duration' );
      $interview_mod->limit( 1 );
      $interview_mod->offset( floor( $row['total']/2 ) );
      $interview_data[$data_index]['median_duration'] =
        current( $interview_class_name::select( $interview_sel, $interview_mod ) )['duration'];

      $interview_sel = lib::create( 'database\select' );
      $interview_sel->from( 'interview' );
      $interview_sel->add_column( 'total_stage_duration' );
      $interview_mod = lib::create( 'database\modifier' );
      $interview_mod->where( 'site_id', '=', $row['site_id'] );
      $interview_mod->where( 'interview.study_phase_id', '=', $row['study_phase_id'] );
      $interview_mod->where( 'interview.platform_id', '=', $row['platform_id'] );
      $interview_mod->order( 'total_stage_duration' );
      $interview_mod->limit( 1 );
      $interview_mod->offset( floor( $row['total']/2 ) );
      $interview_data[$data_index]['median_stage_duration'] =
        current( $interview_class_name::select( $interview_sel, $interview_mod ) )['total_stage_duration'];
    }

    $stage_sel = lib::create( 'database\select' );
    $stage_sel->from( 'stage' );
    $stage_sel->add_table_column( 'interview', 'site_id' );
    $stage_sel->add_table_column( 'interview', 'study_phase_id' );
    $stage_sel->add_table_column( 'interview', 'platform_id' );
    $stage_sel->add_column( 'COUNT(*)', 'total', false );
    $stage_mod = lib::create( 'database\modifier' );
    $stage_mod->join( 'interview', 'stage.interview_id', 'interview.id' );
    $join_mod = lib::create( 'database\modifier' );
    $join_mod->where( 'interview.study_phase_id', '=', 'opal_view.study_phase_id', false );
    $join_mod->where( 'interview.platform_id', '=', 'opal_view.platform_id', false );
    $stage_mod->join_modifier( 'opal_view', $join_mod );
    $stage_mod->where( 'opal_view.keep_updated', '=', true );
    $stage_mod->group( 'site_id' );
    $stage_mod->group( 'study_phase_id' );
    $stage_mod->group( 'platform_id' );
    
    $stage_data = array();
    foreach( $stage_class_name::select( $stage_sel, $stage_mod ) as $row )
    {
      $data_index = sprintf( '%d-%d-%d', $row['site_id'], $row['study_phase_id'], $row['platform_id'] );
      $stage_data[$data_index] = $row['total'];
    }

    $indicator_sel = lib::create( 'database\select' );
    $indicator_sel->from( 'indicator' );
    $indicator_sel->add_table_column( 'interview', 'site_id' );
    $indicator_sel->add_table_column( 'interview', 'study_phase_id' );
    $indicator_sel->add_table_column( 'interview', 'platform_id' );
    $indicator_sel->add_column( 'COUNT(*)', 'total', false );
    $indicator_mod = lib::create( 'database\modifier' );
    $indicator_mod->join( 'stage', 'indicator.stage_type_id', 'stage.stage_type_id' );
    $indicator_mod->join( 'interview', 'stage.interview_id', 'interview.id' );
    $join_mod = lib::create( 'database\modifier' );
    $join_mod->where( 'interview.study_phase_id', '=', 'opal_view.study_phase_id', false );
    $join_mod->where( 'interview.platform_id', '=', 'opal_view.platform_id', false );
    $indicator_mod->join_modifier( 'opal_view', $join_mod );
    $indicator_mod->where( 'opal_view.keep_updated', '=', true );
    $indicator_mod->group( 'site_id' );
    $indicator_mod->group( 'study_phase_id' );
    $indicator_mod->group( 'platform_id' );
    
    $indicator_data = array();
    foreach( $indicator_class_name::select( $indicator_sel, $indicator_mod ) as $row )
    {
      $data_index = sprintf( '%d-%d-%d', $row['site_id'], $row['study_phase_id'], $row['platform_id'] );
      $indicator_data[$data_index] = $row['total'];
    }

    $adverse_event_sel = lib::create( 'database\select' );
    $adverse_event_sel->from( 'adverse_event' );
    $adverse_event_sel->add_table_column( 'interview', 'site_id' );
    $adverse_event_sel->add_table_column( 'interview', 'study_phase_id' );
    $adverse_event_sel->add_table_column( 'interview', 'platform_id' );
    $adverse_event_sel->add_column( 'COUNT(*)', 'total', false );
    $adverse_event_mod = lib::create( 'database\modifier' );
    $adverse_event_mod->join( 'stage', 'adverse_event.stage_id', 'stage.id' );
    $adverse_event_mod->join( 'interview', 'stage.interview_id', 'interview.id' );
    $join_mod = lib::create( 'database\modifier' );
    $join_mod->where( 'interview.study_phase_id', '=', 'opal_view.study_phase_id', false );
    $join_mod->where( 'interview.platform_id', '=', 'opal_view.platform_id', false );
    $adverse_event_mod->join_modifier( 'opal_view', $join_mod );
    $adverse_event_mod->where( 'opal_view.keep_updated', '=', true );
    $adverse_event_mod->group( 'site_id' );
    $adverse_event_mod->group( 'study_phase_id' );
    $adverse_event_mod->group( 'platform_id' );
    
    $adverse_event_data = array();
    foreach( $adverse_event_class_name::select( $adverse_event_sel, $adverse_event_mod ) as $row )
    {
      $data_index = sprintf( '%d-%d-%d', $row['site_id'], $row['study_phase_id'], $row['platform_id'] );
      $adverse_event_data[$data_index] = $row['total'];
    }

    $indicator_outlier_sel = lib::create( 'database\select' );
    $indicator_outlier_sel->from( 'outlier' );
    $indicator_outlier_sel->add_column( 'site_id' );
    $indicator_outlier_sel->add_table_column( 'opal_view', 'study_phase_id' );
    $indicator_outlier_sel->add_table_column( 'opal_view', 'platform_id' );
    $indicator_outlier_sel->add_table_column( 'outlier', 'type' );
    $indicator_outlier_sel->add_column( 'COUNT(*)', 'total', false );
    $indicator_outlier_mod = lib::create( 'database\modifier' );
    $indicator_outlier_mod->join( 'indicator', 'outlier.indicator_id', 'indicator.id' );
    $indicator_outlier_mod->join( 'stage_type', 'indicator.stage_type_id', 'stage_type.id' );
    $indicator_outlier_mod->join( 'opal_view', 'stage_type.opal_view_id', 'opal_view.id' );
    $indicator_outlier_mod->where( 'opal_view.keep_updated', '=', true );
    $indicator_outlier_mod->group( 'site_id' );
    $indicator_outlier_mod->group( 'study_phase_id' );
    $indicator_outlier_mod->group( 'platform_id' );
    $indicator_outlier_data = array();
    foreach( $outlier_class_name::select( $indicator_outlier_sel, $indicator_outlier_mod ) as $row )
    {
      $data_index = sprintf( '%d-%d-%d-%s', $row['site_id'], $row['study_phase_id'], $row['platform_id'], $row['type'] );
      $indicator_outlier_data[$data_index] = $row['total'];
    }

    $stage_outlier_sel = lib::create( 'database\select' );
    $stage_outlier_sel->from( 'outlier' );
    $stage_outlier_sel->add_column( 'site_id' );
    $stage_outlier_sel->add_table_column( 'opal_view', 'study_phase_id' );
    $stage_outlier_sel->add_table_column( 'opal_view', 'platform_id' );
    $stage_outlier_sel->add_table_column( 'outlier', 'type' );
    $stage_outlier_sel->add_column( 'COUNT(*)', 'total', false );
    $stage_outlier_mod = lib::create( 'database\modifier' );
    $stage_outlier_mod->join( 'stage', 'outlier.stage_id', 'stage.id' );
    $stage_outlier_mod->join( 'stage_type', 'stage.stage_type_id', 'stage_type.id' );
    $stage_outlier_mod->join( 'opal_view', 'stage_type.opal_view_id', 'opal_view.id' );
    $stage_outlier_mod->where( 'outlier.indicator_id', '=', NULL );
    $stage_outlier_mod->where( 'opal_view.keep_updated', '=', true );
    $stage_outlier_mod->group( 'site_id' );
    $stage_outlier_mod->group( 'study_phase_id' );
    $stage_outlier_mod->group( 'platform_id' );
    $stage_outlier_data = array();
    foreach( $outlier_class_name::select( $stage_outlier_sel, $stage_outlier_mod ) as $row )
    {
      $data_index = sprintf( '%d-%d-%d-%s', $row['site_id'], $row['study_phase_id'], $row['platform_id'], $row['type'] );
      $stage_outlier_data[$data_index] = $row['total'];
    }

    $indicator_issue_sel = lib::create( 'database\select' );
    $indicator_issue_sel->from( 'indicator_issue' );
    $indicator_issue_sel->add_table_column( 'technician', 'site_id' );
    $indicator_issue_sel->add_table_column( 'opal_view', 'study_phase_id' );
    $indicator_issue_sel->add_table_column( 'opal_view', 'platform_id' );
    $indicator_issue_sel->add_column( 'closed' );
    $indicator_issue_sel->add_column( 'COUNT(*)', 'total', false );
    $indicator_issue_mod = lib::create( 'database\modifier' );
    $indicator_issue_mod->join( 'technician', 'indicator_issue.technician_id', 'technician.id' );
    $indicator_issue_mod->join( 'indicator', 'indicator_issue.indicator_id', 'indicator.id' );
    $indicator_issue_mod->join( 'stage_type', 'indicator.stage_type_id', 'stage_type.id' );
    $indicator_issue_mod->join( 'opal_view', 'stage_type.opal_view_id', 'opal_view.id' );
    $indicator_issue_mod->where( 'opal_view.keep_updated', '=', true );
    $indicator_issue_mod->group( 'site_id' );
    $indicator_issue_mod->group( 'study_phase_id' );
    $indicator_issue_mod->group( 'platform_id' );
    $indicator_issue_mod->group( 'closed' );
    $indicator_issue_data = array();
    foreach( $indicator_issue_class_name::select( $indicator_issue_sel, $indicator_issue_mod ) as $row )
    {
      $data_index = sprintf( '%d-%d-%d-%d', $row['site_id'], $row['study_phase_id'], $row['platform_id'], $row['closed'] );
      $indicator_issue_data[$data_index] = $row['total'];
    }

    $stage_issue_sel = lib::create( 'database\select' );
    $stage_issue_sel->from( 'stage_issue' );
    $stage_issue_sel->add_table_column( 'technician', 'site_id' );
    $stage_issue_sel->add_table_column( 'opal_view', 'study_phase_id' );
    $stage_issue_sel->add_table_column( 'opal_view', 'platform_id' );
    $stage_issue_sel->add_column( 'type' );
    $stage_issue_sel->add_column( 'closed' );
    $stage_issue_sel->add_column( 'COUNT(*)', 'total', false );
    $stage_issue_mod = lib::create( 'database\modifier' );
    $stage_issue_mod->join( 'technician', 'stage_issue.technician_id', 'technician.id' );
    $stage_issue_mod->join( 'stage_type', 'stage_issue.stage_type_id', 'stage_type.id' );
    $stage_issue_mod->join( 'opal_view', 'stage_type.opal_view_id', 'opal_view.id' );
    $stage_issue_mod->where( 'opal_view.keep_updated', '=', true );
    $stage_issue_mod->group( 'site_id' );
    $stage_issue_mod->group( 'study_phase_id' );
    $stage_issue_mod->group( 'platform_id' );
    $stage_issue_mod->group( 'type' );
    $stage_issue_mod->group( 'closed' );
    $stage_issue_data = array();
    foreach( $stage_issue_class_name::select( $stage_issue_sel, $stage_issue_mod ) as $row )
    {
      $data_index = sprintf(
        '%d-%d-%d-%s-%d',
        $row['site_id'],
        $row['study_phase_id'],
        $row['platform_id'],
        $row['type'],
        $row['closed']
      );
      $stage_issue_data[$data_index] = $row['total'];
    }

    $site_mod = lib::create( 'database\modifier' );
    $site_mod->order( 'site.name' );
    if( !$db_role->all_sites ) $site_mod->where( 'site.id', '=', $db_site->id );
    foreach( $db_application->get_site_object_list( $site_mod ) as $db_site )
    {
      $site_node = $this->add_root_item( $db_site->name );

      $opal_view_sel = lib::create( 'database\select' );
      $opal_view_sel->add_column( 'id' );
      $opal_view_sel->add_column( 'study_phase_id' );
      $opal_view_sel->add_table_column( 'study_phase', 'name', 'study_phase' );
      $opal_view_sel->add_column( 'platform_id' );
      $opal_view_sel->add_table_column( 'platform', 'name', 'platform' );
      $opal_view_mod = lib::create( 'database\modifier' );
      $opal_view_mod->join( 'study_phase', 'opal_view.study_phase_id', 'study_phase.id' );
      $opal_view_mod->join( 'platform', 'opal_view.platform_id', 'platform.id' );
      $opal_view_mod->where( 'opal_view.keep_updated', '=', true );
      $opal_view_mod->order( 'study_phase.rank' );
      $opal_view_mod->order( 'platform.name' );
      foreach( $opal_view_class_name::select( $opal_view_sel, $opal_view_mod ) as $opal_view )
      {
        $data_index = sprintf( '%d-%d-%d', $db_site->id, $opal_view['study_phase_id'], $opal_view['platform_id'] );
        $opal_view_node = $this->add_item( $site_node, sprintf( '%s: %s', $opal_view['study_phase'], $opal_view['platform'] ) );

        $interviews = array_key_exists( $data_index, $interview_data ) ? $interview_data[$data_index]['total'] : 0;
        $this->add_item( $opal_view_node, 'Interviews', $interviews );

        if( 0 < $interviews )
        {
          $this->add_item( $opal_view_node, 'Median Duration', sprintf(
            'Stage: %s, Total: %s',
            $interview_data[$data_index]['median_stage_duration'],
            $interview_data[$data_index]['median_duration']
          ) );

          $this->add_item( $opal_view_node, 'Date Span', sprintf(
            '%s to %s',
            util::get_datetime_object( $interview_data[$data_index]['min_date'] )->format( 'F jS, Y' ),
            util::get_datetime_object( $interview_data[$data_index]['max_date'] )->format( 'F jS, Y' )
          ) );

          $low_data_index = sprintf( '%s-low', $data_index );
          $high_data_index = sprintf( '%s-high', $data_index );
          $low_value = array_key_exists( $low_data_index, $indicator_outlier_data ) ? $indicator_outlier_data[$low_data_index] : 0;
          $high_value = array_key_exists( $high_data_index, $indicator_outlier_data ) ? $indicator_outlier_data[$high_data_index] : 0;
          $this->add_item(
            $opal_view_node,
            'Indicator Outliers',
            sprintf(
              'Low: %d (%0.2f%%), High: %d (%0.2f%%)',
              $low_value,
              $low_value / $indicator_data[$data_index],
              $high_value,
              $high_value / $indicator_data[$data_index]
            )
          );

          $low_value = array_key_exists( $low_data_index, $stage_outlier_data ) ? $stage_outlier_data[$low_data_index] : 0;
          $high_value = array_key_exists( $high_data_index, $stage_outlier_data ) ? $stage_outlier_data[$high_data_index] : 0;
          $this->add_item(
            $opal_view_node,
            'Stage Outliers',
            sprintf(
              'Low: %d (%0.2f%%), High: %d (%0.2f%%)',
              $low_value,
              $low_value / $stage_data[$data_index],
              $high_value,
              $high_value / $stage_data[$data_index]
            )
          );

          $open_data_index = sprintf( '%s-0', $data_index );
          $closed_data_index = sprintf( '%s-1', $data_index );
          $this->add_item(
            $opal_view_node,
            'Indicator Issues',
            sprintf(
              'Open: %d, Closed: %d',
              array_key_exists( $open_data_index, $indicator_issue_data ) ? $indicator_issue_data[$open_data_index] : 0,
              array_key_exists( $closed_data_index, $indicator_issue_data ) ? $indicator_issue_data[$closed_data_index] : 0
            )
          );

          $open_data_index = sprintf( '%s-duration-0', $data_index );
          $closed_data_index = sprintf( '%s-duration-1', $data_index );
          $this->add_item(
            $opal_view_node,
            'Stage Duration Issues',
            sprintf(
              'Open: %d, Closed: %d',
              array_key_exists( $open_data_index, $stage_issue_data ) ? $stage_issue_data[$open_data_index] : 0,
              array_key_exists( $closed_data_index, $stage_issue_data ) ? $stage_issue_data[$closed_data_index] : 0
            )
          );

          $open_data_index = sprintf( '%s-skip-0', $data_index );
          $closed_data_index = sprintf( '%s-skip-1', $data_index );
          $this->add_item(
            $opal_view_node,
            'Stage Skip Issues',
            sprintf(
              'Open: %d, Closed: %d',
              array_key_exists( $open_data_index, $stage_issue_data ) ? $stage_issue_data[$open_data_index] : 0,
              array_key_exists( $closed_data_index, $stage_issue_data ) ? $stage_issue_data[$closed_data_index] : 0
            )
          );

          $this->add_item(
            $opal_view_node,
            'Adverse Events',
            array_key_exists( $data_index, $adverse_event_data ) ? $adverse_event_data[$data_index] : 0
          );
        }
      }
    }
  }
}
