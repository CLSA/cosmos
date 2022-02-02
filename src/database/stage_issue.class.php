<?php
/**
 * stage_issue.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\database;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * stage_issue: record
 */
class stage_issue extends \cenozo\database\record
{
  /**
   * Generates all issues from the last month (should be run on the 1st of the month
   */
  public static function generate_issues()
  {
    $threshold = lib::create( 'business\setting_manager' )->get_setting( 'general', 'issue_threshold' );

    // create all stage_issue records for this month
    $stage_issue_sel = lib::create( 'database\select' );
    $stage_issue_sel->from( 'outlier' );
    $stage_issue_sel->add_constant( NULL, 'create_timestamp' );
    $stage_issue_sel->add_column( 'technician_id' );
    $stage_issue_sel->add_column( 'stage_type.id', 'stage_type_id', false );
    $stage_issue_sel->add_constant( 'DATE( UTC_TIMESTAMP() - INTERVAL 1 MONTH )', 'date', 'date', false );
    
    $stage_issue_mod = lib::create( 'database\modifier' );
    $stage_issue_mod->join( 'stage', 'outlier.stage_id', 'stage.id' );
    $stage_issue_mod->join( 'stage_type', 'stage.stage_type_id', 'stage_type.id' );
    $stage_issue_mod->where( 'outlier.technician_id', '!=', NULL );
    $stage_issue_mod->where( 'outlier.indicator_id', '=', NULL );
    $stage_issue_mod->where( 'MONTH( outlier.date )', '=', 'MONTH( UTC_TIMESTAMP() - INTERVAL 1 MONTH )', false );
    $stage_issue_mod->group( 'outlier.technician_id' );
    $stage_issue_mod->group( 'stage.stage_type_id' );
    $stage_issue_mod->having( 'COUNT(*)', '>=', $threshold );

    static::db()->execute( sprintf(
      'INSERT IGNORE INTO stage_issue( create_timestamp, technician_id, stage_type_id, date ) %s %s',
      $stage_issue_sel->get_sql(),
      $stage_issue_mod->get_sql()
    ) );

    // associate all stages with the new stage issues
    $stage_sel = lib::create( 'database\select' );
    $stage_sel->from( 'stage_issue' );
    $stage_sel->add_constant( NULL, 'create_timestamp' );
    $stage_sel->add_column( 'id' );
    $stage_sel->add_column( 'stage.id', 'stage_id', false );

    $stage_mod = lib::create( 'database\modifier' );
    $stage_mod->join( 'stage_type', 'stage_issue.stage_type_id', 'stage_type.id' );
    $stage_mod->join( 'stage', 'stage_type.id', 'stage.stage_type_id' );
    $join_mod = lib::create( 'database\modifier' );
    $join_mod->where( 'stage_issue.technician_id', '=', 'outlier.technician_id', false );
    $join_mod->where( 'stage.id', '=', 'outlier.stage_id', false );
    $join_mod->where( 'outlier.indicator_id', '=', NULL );
    $stage_mod->join_modifier( 'outlier', $join_mod );

    static::db()->execute( sprintf(
      'INSERT IGNORE INTO stage_issue_has_stage( create_timestamp, stage_issue_id, stage_id ) %s %s',
      $stage_sel->get_sql(),
      $stage_mod->get_sql()
    ) );
  }
}
