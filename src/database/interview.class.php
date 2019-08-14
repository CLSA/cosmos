<?php
/**
 * interview.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\database;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * interview: record
 */
class interview extends \cenozo\database\record
{
  /**
   * Adds any data files from Opal which don't already exist
   * 
   * @access public
   */
  public static function update_interview_list()
  {
    $platform_class_name = lib::get_class_name( 'database\platform' );
    $study_phase_class_name = lib::get_class_name( 'database\study_phase' );
    $participant_class_name = lib::get_class_name( 'database\participant' );
    $site_class_name = lib::get_class_name( 'database\site' );
    $interview_class_name = lib::get_class_name( 'database\interview' );
    $technician_class_name = lib::get_class_name( 'database\technician' );
    $user_class_name = lib::get_class_name( 'database\user' );

    $opal_manager = lib::create( 'business\opal_manager' );
    $db_application = lib::create( 'business\session' )->get_application();

    $site_sel = lib::create( 'database\select' );
    $site_sel->add_column( 'id' );
    $site_sel->add_column( 'name' );
    $site_id_list = array();
    foreach( $db_application->get_site_list( $site_sel ) as $site ) $site_id_list[$site['name']] = $site['id'];

    $max_count = 0;

    // loop through all study phases (bl, f1, f2, etc)
    foreach( $study_phase_class_name::select_objects() as $db_study_phase )
    {
      // loop through all data sources (inhome, dcs, dcs_home, dcs_phone)
      foreach( $platform_class_name::select_objects() as $db_platform )
      {

        // get a list of all stages for this study-phase and platform
        $stage_type_sel = lib::create( 'database\select' );
        $stage_type_sel->add_column( 'id' );
        $stage_type_sel->add_column( 'name' );
        
        $stage_type_mod = lib::create( 'database\modifier' );
        $stage_type_mod->where( 'platform_id', '=', $db_platform->id );
        $stage_type_list = $db_study_phase->get_stage_type_list( $stage_type_sel, $stage_type_mod );

        if( 0 < count( $stage_type_list ) )
        {
          // an array to keep track of uid=>interview_id
          $interview_id_list = array();

          // an array to keep track of uid->technician_site_id
          $technician_site_id_list = array();

          $project_name = sprintf( 'cosmos_%s', $db_platform->name );
          $opal_interview_view = sprintf( 'QC_%s_interview', strtoupper( $db_study_phase->code ) );

          // get interview data
          $values = $opal_manager->get_all_values( $project_name, $opal_interview_view );

          foreach( $values as $uid => $array )
          {
            // ignore if the barcode is null (the interview hasn't been exported yet)
            if( !$array['barcode'] ) continue;

            // get the participant_id
            $participant_sel = lib::create( 'database\select' );
            $participant_sel->add_column( 'id' );
            $participant_mod = lib::create( 'database\modifier' );
            $participant_mod->where( 'uid', '=', $uid );
            $participant_id = current( $participant_class_name::select( $participant_sel, $participant_mod ) )['id'];
            if( false === $participant_id )
              throw lib::create( 'exception\runtime',
                sprintf( 'Invalid UID "%s" returned from Opal view "%s"', $uid, $opal_interview_view ),
                __METHOD__ );

            // get the site_id
            if( !array_key_exists( $array['site'], $site_id_list ) )
              throw lib::create( 'exception\runtime',
                sprintf( 'Invalid site name "%s" returned from Opal view "%s"', $array['site'], $opal_interview_view ),
                __METHOD__ );
            $site_id = $site_id_list[$array['site']];

            // only add the interview record if it doesn't already exist
            $interview_mod = lib::create( 'database\modifier' );
            $interview_mod->where( 'participant_id', '=', $participant_id );
            $interview_mod->where( 'study_phase_id', '=', $db_study_phase->id );
            $interview_mod->where( 'platform_id', '=', $db_platform->id );
            if( 0 == $interview_class_name::count( $interview_mod ) )
            {
              $db_interview = lib::create( 'database\interview' );
              $db_interview->participant_id = $participant_id;
              $db_interview->study_phase_id = $db_study_phase->id;
              $db_interview->platform_id = $db_platform->id;
              $db_interview->site_id = $site_id;
              $db_interview->start_date = $array['start_date'];
              $db_interview->barcode = $array['barcode'];
              $db_interview->duration = $array['duration'];
              $db_interview->total_stage_duration = $array['stage_duration'];
              $db_interview->save();
              $interview_id_list[$uid] = $db_interview->id;
              $technician_site_id_list[$uid] = $site_id;
            }
          }

          if( $max_count < count( $interview_id_list ) ) $max_count = count( $interview_id_list );

          // now read the data from all stage type views (but only if interviews were found)
          if( 0 < count( $interview_id_list ) ) foreach( $stage_type_list as $stage_type )
          {
            $opal_stage_view = sprintf( 'QC_%s_%s', strtoupper( $db_study_phase->code ), $stage_type['name'] );
            $values = $opal_manager->get_all_values( $project_name, $opal_stage_view );

            foreach( $values as $uid => $array )
            {
              // ignore UIDs which already exist
              if( !array_key_exists( $uid, $interview_id_list ) ) continue;

              $db_technician = NULL;
              if( !is_null( $array['technician'] ) )
              {
                // get the technician or create one if they don't already exist
                $db_technician = $technician_class_name::get_unique_record(
                  array( 'site_id', 'name' ),
                  array( $technician_site_id_list[$uid], $array['technician'] )
                );
                if( is_null( $db_technician ) )
                {
                  $db_technician = lib::create( 'database\technician' );
                  $db_technician->site_id = $technician_site_id_list[$uid];
                  $db_technician->name = $array['technician'];

                  // if the user name matches a cenozo user name then link them
                  $db_user = $user_class_name::get_unique_record( 'name', $array['technician'] );
                  if( !is_null( $db_user ) ) $db_technician->user_id = $db_user->id;

                  $db_technician->save();
                }
              }

              // create the stage entry
              $db_stage = lib::create( 'database\stage' );
              $db_stage->interview_id = $interview_id_list[$uid];
              $db_stage->stage_type_id = $stage_type['id'];
              $db_stage->technician_id = is_null( $db_technician ) ? NULL : $db_technician->id;
              $db_stage->contraindicated = 'true' == $array['contraindicated'];
              $db_stage->missing = 'true' == $array['missing'];
              $db_stage->skip = $array['skip'];
              $db_stage->duration = $array['duration'];
              $db_stage->save();

              // store the stage data
              $db_stage_data = lib::create( sprintf(
                'database\%s_%s_%s_data',
                $db_study_phase->code,
                $db_platform->name,
                $stage_type['name']
              ) );
              $db_stage_data->stage_id = $db_stage->id;

              foreach( $array as $key => $value )
              {
                if( 'meta_' == substr( $key, 0, 5 ) || '_name' == substr( $key, -5 ) || is_null( $value ) )
                {
                  // ignore meta and name data, and empty values
                }
                else if( $db_stage_data->column_exists( $key ) )
                {
                  // if the column is in the data table then write it
                  $db_stage_data->$key = $value;
                }
                else if( '_value' == substr( $key, -6 ) )
                {
                  // process all values using the corresponding *_name column for the column names
                  foreach( $array[str_replace( '_value', '_name', $key )] as $index => $name )
                  {
                    $column = sprintf( '%s_%s', strtolower( $name ), substr( $key, 0, -6 ) );
                    if( array_key_exists( $index, $value ) ) $db_stage_data->$column = $value[$index];
                  }
                }
                else if( '_size' == substr( $key, -5 ) )
                {
                  // process all values using the corresponding *_name column for the column names
                  foreach( $array[str_replace( '_size', '_name', $key )] as $index => $name )
                  {
                    $column = sprintf( '%s_%s', strtolower( $name ), substr( $key, 0, -5 ) );
                    if( array_key_exists( $index, $value ) ) $db_stage_data->$column = $value[$index];
                  }
                }
              }
              $db_stage_data->save();

              // store any stage comments
              if( array_key_exists( 'comment', $array ) && !is_null( $array['comment'] ) )
              {
                $comment_obj = util::json_decode( $array['comment'] );
                foreach( $comment_obj as $type => $note_list )
                {
                  foreach( $note_list as $note )
                  {
                    $db_comment = lib::create( 'database\comment' );
                    $db_comment->stage_id = $db_stage->id;
                    $db_comment->type = $type;
                    $db_comment->note = $note;
                    $db_comment->save();
                  }
                }
              }
            }
          }
        }
      }
    }

    // update all outliers since we now have new data
    if( 0 < $max_count ) self::db()->execute( 'CALL update_outliers()' );

    return $max_count;
  }
}
