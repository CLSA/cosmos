<?php
/**
 * site.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\database;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * site: record
 */
class site extends \cenozo\database\site
{
  /** 
   * Extend parent method since site and indicator_issue and stage_issue do not have a regular 1 to N relationship
   */
  public function get_record_list( $record_type, $select = NULL, $modifier = NULL, $return_alternate = '', $distinct = false )
  {
    $return_value = NULL;

    if( 'indicator_issue' == $record_type )
    {   
      $indicator_issue_class_name = lib::get_class_name( 'database\indicator_issue' );

      if( !is_null( $select ) && !is_a( $select, lib::get_class_name( 'database\select' ) ) ) 
        throw lib::create( 'exception\argument', 'select', $select, __METHOD__ );
      if( !is_null( $modifier ) && !is_a( $modifier, lib::get_class_name( 'database\modifier' ) ) ) 
        throw lib::create( 'exception\argument', 'modifier', $modifier, __METHOD__ );
      if( !is_string( $return_alternate ) ) 
        throw lib::create( 'exception\argument', 'return_alternate', $return_alternate, __METHOD__ );

      if( is_null( $modifier ) ) $modifier = lib::create( 'database\modifier' );

      $modifier->join( 'technician', 'indicator_issue.technician_id', 'technician.id' );
      $modifier->where( 'technician.site_id', '=', $this->id );

      if( 'count' == $return_alternate )
      {   
        $return_value = $indicator_issue_class_name::count( $modifier, $distinct );
      }   
      else
      {   
        $return_value = 'object' == $return_alternate
                      ? $indicator_issue_class_name::select_objects( $modifier )
                      : $indicator_issue_class_name::select( $select, $modifier );
      }   
    }
    else if( 'stage_issue' == $record_type )
    {   
      $stage_issue_class_name = lib::get_class_name( 'database\stage_issue' );

      if( !is_null( $select ) && !is_a( $select, lib::get_class_name( 'database\select' ) ) ) 
        throw lib::create( 'exception\argument', 'select', $select, __METHOD__ );
      if( !is_null( $modifier ) && !is_a( $modifier, lib::get_class_name( 'database\modifier' ) ) ) 
        throw lib::create( 'exception\argument', 'modifier', $modifier, __METHOD__ );
      if( !is_string( $return_alternate ) ) 
        throw lib::create( 'exception\argument', 'return_alternate', $return_alternate, __METHOD__ );

      if( is_null( $modifier ) ) $modifier = lib::create( 'database\modifier' );

      $modifier->join( 'technician', 'stage_issue.technician_id', 'technician.id' );
      $modifier->where( 'technician.site_id', '=', $this->id );

      if( 'count' == $return_alternate )
      {   
        $return_value = $stage_issue_class_name::count( $modifier, $distinct );
      }   
      else
      {   
        $return_value = 'object' == $return_alternate
                      ? $stage_issue_class_name::select_objects( $modifier )
                      : $stage_issue_class_name::select( $select, $modifier );
      }   
    }   
    else
    {   
      $return_value = parent::get_record_list( $record_type, $select, $modifier, $return_alternate, $distinct );
    }   

    return $return_value;
  }
}
