<?php
/**
 * module.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\service\indicator_issue_note;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * Performs operations which effect how this module is used in a service
 */
class module extends \cenozo\service\site_restricted_module
{
  /** 
   * Extend parent method
   */
  public function validate()
  {
    parent::validate();

    if( 300 > $this->get_status()->get_code() )
    {   
      $session = lib::create( 'business\session' );
      $db_user = $session->get_user();
      $db_role = $session->get_role();
      $db_indicator_issue_note = $this->get_resource();

      if( !is_null( $db_indicator_issue_note ) ) 
      {   
        // restrict by site
        $db_restrict_site = $this->get_restricted_site();
        if( !is_null( $db_restrict_site ) ) 
        {
          if( $db_indicator_issue_note->get_indicator_issue()->get_technician()->site_id != $db_restrict_site->id )
            $this->get_status()->set_code( 403 );
        }

        if( 3 > $db_role->tier )
        {
          $method = $this->get_method();
          if( // only admins can edit or delete notes other than the most recent one
              ( in_array( $method, [ 'DELETE', 'PATCH' ] ) && !$db_indicator_issue_note->is_most_recent() ) ||
              // only admins can edit other user's notes
              ( 'PATCH' == $method && $db_indicator_issue_note->user_id != $db_user->id ) )
            $this->get_status()->set_code( 403 );
        }
      }   
    }   
  }

  /**
   * Extend parent method
   */
  public function prepare_read( $select, $modifier )
  {
    parent::prepare_read( $select, $modifier );

    $modifier->join( 'user', 'indicator_issue_note.user_id', 'user.id' );
    $modifier->join( 'indicator_issue', 'indicator_issue_note.indicator_issue_id', 'indicator_issue.id' );
    $modifier->join( 'technician', 'indicator_issue.technician_id', 'technician.id' );
    $modifier->join( 'site', 'technician.site_id', 'site.id' );
    $modifier->join( 'indicator', 'indicator_issue.indicator_id', 'indicator.id' );
    $modifier->join( 'stage_type', 'indicator.stage_type_id', 'stage_type.id' );

    // include supplemental data
    $db_indicator_issue_note = $this->get_resource();
    if( !is_null( $db_indicator_issue_note ) )
    {
      $select->add_table_column(
        'user',
        'CONCAT( user.first_name, " ", user.last_name, " (", user.name, ")" )',
        'formatted_user_id',
        false
      );
      $select->add_constant( $db_indicator_issue_note->is_most_recent(), 'is_most_recent', 'boolean' );
    }
  }
}
