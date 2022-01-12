<?php
/**
 * query.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\service\opal_view;
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

    if( $this->get_argument( 'upload', false ) && 3 > lib::create( 'business\session' )->get_role()->tier )
      $this->status->set_code( 403 );
  }

  /**
   * Extends parent method
   */
  protected function execute()
  {
    parent::execute();

    if( $this->get_argument( 'upload', false ) )
    {
      $opal_view_class_name = lib::get_class_name( 'database\opal_view' );
      $this->set_data( $opal_view_class_name::update_opal_view_list() );
    }
  }
}
