<?php
/**
 * indicator_issue_note.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\database;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * indicator_issue_note: record
 */
class indicator_issue_note extends \cenozo\database\record
{
  /**
   * Determins if this is the most recent note in the indicator_issue
   * @return boolean
   */
  public function is_most_recent()
  {
    $select = lib::create( 'database\select' );
    $select->add_table_column( 'indicator_issue_note', 'id' );
    $modifier = lib::create( 'database\modifier' );
    $modifier->order_desc( 'datetime' );
    $modifier->limit( 1 );
    $indicator_issue_note = current( $this->get_indicator_issue()->get_indicator_issue_note_list( $select, $modifier ) );
    return $indicator_issue_note['id'] == $this->id;
  }
}
