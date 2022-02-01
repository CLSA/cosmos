<?php
/**
 * post.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\service\stage_issue\stage_issue_note;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * The base class of all post services.
 */
class post extends \cenozo\service\post
{
  /**
   * Extends parent method
   */
  protected function prepare()
  {
    parent::prepare();

    // set the user and date
    $db_stage_issue_note = $this->get_leaf_record();
    $db_stage_issue_note->user_id = lib::create( 'business\session' )->get_user()->id;
    $db_stage_issue_note->datetime = util::get_datetime_object();
  }
}
