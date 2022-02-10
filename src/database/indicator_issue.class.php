<?php
/**
 * indicator_issue.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\database;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * indicator_issue: record
 */
class indicator_issue extends \cenozo\database\record
{
  /**
   * Generates all issues from the last month (should be run on the 1st of the month
   */
  public static function generate_issues()
  {
    $threshold = lib::create( 'business\setting_manager' )->get_setting( 'general', 'issue_threshold' );

    // create all indicator_issue records for this month
    $indicator_issue_sel = lib::create( 'database\select' );
    $indicator_issue_sel->from( 'outlier' );
    $indicator_issue_sel->add_constant( NULL, 'create_timestamp' );
    $indicator_issue_sel->add_column( 'technician_id' );
    $indicator_issue_sel->add_column( 'indicator.id', 'indicator_id', false );
    $indicator_issue_sel->add_constant( 'DATE( UTC_TIMESTAMP() - INTERVAL 1 MONTH )', 'date', 'date', false );
    
    $indicator_issue_mod = lib::create( 'database\modifier' );
    $indicator_issue_mod->join( 'stage', 'outlier.stage_id', 'stage.id' );
    $indicator_issue_mod->join( 'indicator', 'outlier.indicator_id', 'indicator.id' );
    $indicator_issue_mod->where( 'outlier.technician_id', '!=', NULL );
    $indicator_issue_mod->where( 'YEAR( outlier.date )', '=', 'YEAR( UTC_TIMESTAMP() - INTERVAL 1 MONTH )', false );
    $indicator_issue_mod->where( 'MONTH( outlier.date )', '=', 'MONTH( UTC_TIMESTAMP() - INTERVAL 1 MONTH )', false );
    $indicator_issue_mod->group( 'outlier.technician_id' );
    $indicator_issue_mod->group( 'outlier.indicator_id' );
    $indicator_issue_mod->having( 'COUNT(*)', '>=', $threshold );

    static::db()->execute( sprintf(
      'INSERT IGNORE INTO indicator_issue( create_timestamp, technician_id, indicator_id, date ) %s %s',
      $indicator_issue_sel->get_sql(),
      $indicator_issue_mod->get_sql()
    ) );

    // associate all stages with the new indicator issues
    $stage_sel = lib::create( 'database\select' );
    $stage_sel->from( 'indicator_issue' );
    $stage_sel->add_constant( NULL, 'create_timestamp' );
    $stage_sel->add_column( 'id' );
    $stage_sel->add_column( 'stage.id', 'stage_id', false );

    $stage_mod = lib::create( 'database\modifier' );
    $stage_mod->join( 'indicator', 'indicator_issue.indicator_id', 'indicator.id' );
    $join_mod = lib::create( 'database\modifier' );
    $join_mod->where( 'indicator.id', '=', 'outlier.indicator_id', false );
    $join_mod->where( 'indicator_issue.technician_id', '=', 'outlier.technician_id', false );
    $stage_mod->join_modifier( 'outlier', $join_mod );
    $stage_mod->join( 'stage', 'outlier.stage_id', 'stage.id' );
    $stage_mod->where( 'YEAR( outlier.date )', '=', 'YEAR( UTC_TIMESTAMP() - INTERVAL 1 MONTH )', false );
    $stage_mod->where( 'MONTH( outlier.date )', '=', 'MONTH( UTC_TIMESTAMP() - INTERVAL 1 MONTH )', false );

    static::db()->execute( sprintf(
      'INSERT IGNORE INTO indicator_issue_has_stage( create_timestamp, indicator_issue_id, stage_id ) %s %s',
      $stage_sel->get_sql(),
      $stage_mod->get_sql()
    ) );
  }
}
