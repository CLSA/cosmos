<?php
/**
 * stage_issue_note.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\database;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * stage_issue_note: record
 */
class stage_issue_note extends \cenozo\database\record
{
  /**
   * Determins if this is the most recent note in the stage_issue
   * @return boolean
   */
  public function is_most_recent()
  {
    $select = lib::create( 'database\select' );
    $select->add_table_column( 'stage_issue_note', 'id' );
    $modifier = lib::create( 'database\modifier' );
    $modifier->order_desc( 'datetime' );
    $modifier->limit( 1 );
    $stage_issue_note = current( $this->get_stage_issue()->get_stage_issue_note_list( $select, $modifier ) );
    return $stage_issue_note['id'] == $this->id;
  }
}
