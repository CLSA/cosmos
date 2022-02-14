<?php
/**
 * module.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\service\comment;
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
      $db_comment = $this->get_resource();

      if( !is_null( $db_comment ) ) 
      {   
        // restrict by site
        $db_restrict_site = $this->get_restricted_site();
        if( !is_null( $db_restrict_site ) ) 
        {
          if( $db_comment->get_stage()->get_technician()->site_id != $db_restrict_site->id )
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

    $modifier->join( 'stage', 'comment.stage_id', 'stage.id' );
    $modifier->join( 'technician', 'stage.technician_id', 'technician.id' );
    $modifier->join( 'site', 'technician.site_id', 'site.id' );
    $modifier->join( 'stage_type', 'stage.stage_type_id', 'stage_type.id' );
  }
}
