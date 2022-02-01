<?php
/**
 * module.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\service\stage_issue;
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
      // restrict by site
      $db_stage_issue = $this->get_resource();
      $db_restrict_site = $this->get_restricted_site();
      if( !is_null( $db_stage_issue ) && !is_null( $db_restrict_site ) )
      {
        if( $db_restrict_site->id != $db_stage_issue->get_technician()->site_id ) $this->get_status()->set_code( 403 );
      }
    }
  }

  /**
   * Extend parent method
   */
  public function prepare_read( $select, $modifier )
  {
    parent::prepare_read( $select, $modifier );

    $modifier->join( 'technician', 'stage_issue.technician_id', 'technician.id' );
    $modifier->join( 'site', 'technician.site_id', 'site.id' );
    $modifier->join( 'stage_type', 'stage_issue.stage_type_id', 'stage_type.id' );
    $modifier->join( 'opal_view', 'stage_type.opal_view_id', 'opal_view.id' );
    $modifier->join( 'study_phase', 'opal_view.study_phase_id', 'study_phase.id' );
    $modifier->join( 'platform', 'opal_view.platform_id', 'platform.id' );

    $this->add_count_column( 'stage_count', 'stage', $select, $modifier );
    $select->add_column( 'DATE_FORMAT( date, "%M, %Y" )', 'date_string', false );
    $select->add_column(
      'CONCAT( '.
        'REPLACE( REPLACE( REPLACE( sec_to_time( duration_low ), ".999999", "" ), ".000000", "" ), "00:00:", "00:" ), '.
        '" to ", '.
        'REPLACE( REPLACE( REPLACE( sec_to_time( duration_high ), ".999999", "" ), ".000000", "" ), "00:00:", "00:" )'.
      ')',
      'duration_span',
      false
    );

    // restrict by site
    $db_restrict_site = $this->get_restricted_site();
    if( !is_null( $db_restrict_site ) ) $modifier->where( 'site.id', '=', $db_restrict_site->id );
  }
}
