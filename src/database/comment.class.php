<?php
/**
 * comment.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\database;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * comment: record
 */
class comment extends \cenozo\database\has_rank
{
  protected static $rank_parent = 'stage';
}
