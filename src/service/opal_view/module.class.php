<?php
/**
 * module.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\service\opal_view;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * Performs operations which effect how this module is used in a service
 */
class module extends \cenozo\service\module
{
  /**
   * Extend parent method
   */
  public function prepare_read( $select, $modifier )
  {
    parent::prepare_read( $select, $modifier );

    $modifier->join( 'study_phase', 'opal_view.study_phase_id', 'study_phase.id' );
    $modifier->join( 'study', 'study_phase.study_id', 'study.id' );
    $modifier->join( 'platform', 'opal_view.platform_id', 'platform.id' );
  }
}
