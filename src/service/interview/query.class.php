<?php
/**
 * query.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\service\interview;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * Extends parent class
 */
class query extends \cenozo\service\query
{
  /**
   * Extends parent method
   */
  protected function validate()
  {
    parent::validate();

    if( $this->get_argument( 'update', false ) && 3 > lib::create( 'business\session' )->get_role()->tier )
      $this->status->set_code( 403 );
  }

  /**
   * Extends parent method
   */
  protected function execute()
  {
    $interview_class_name = lib::get_class_name( 'database\interview' );
    $indicator_issue_class_name = lib::get_class_name( 'database\indicator_issue' );
    $stage_issue_class_name = lib::get_class_name( 'database\stage_issue' );

    parent::execute();

    if( $this->get_argument( 'update', false ) )
    {
      set_time_limit( 600 ); // set time limit to 10 minutes
      $new_interview_count = $interview_class_name::update_interview_list();

      // only generate last-month's issues on the 1st of the month
      if( '1' == util::get_datetime_object()->format( 'j' ) )
      {
        $indicator_issue_class_name::generate_issues();
        $stage_issue_class_name::generate_issues();
      }
      
      $this->set_data( $new_interview_count );
    }
  }
}
