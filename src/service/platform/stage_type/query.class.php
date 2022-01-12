<?php
/**
 * query.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\service\platform\stage_type;
use cenozo\lib, cenozo\log, cosmos\util;

class query extends \cenozo\service\query
{
  /**
   * Extends parent method
   */
  public function get_leaf_parent_relationship()
  {
    $relationship_class_name = lib::get_class_name( 'database\relationship' );
    return $relationship_class_name::MANY_TO_MANY;
  }

  /**
   * Extends parent method
   */
  protected function get_record_count()
  {
    $stage_type_class_name = lib::get_class_name( 'database\stage_type' );
    $modifier = clone $this->modifier;
    $db_platform = $this->get_parent_record();

    // find aliases in the select and translate them in the modifier
    $this->select->apply_aliases_to_modifier( $modifier );

    // restrict to this opal_view's files
    $modifier->join( 'opal_view', 'stage_type.opal_view_id', 'opal_view.id', '', NULL, true );
    $modifier->where( 'opal_view.platform_id', '=', $db_platform->id );

    return $stage_type_class_name::count( $modifier );
  }

  /**
   * Extends parent method
   */
  protected function get_record_list()
  {
    $stage_type_class_name = lib::get_class_name( 'database\stage_type' );
    $modifier = clone $this->modifier;
    $db_platform = $this->get_parent_record();

    // find aliases in the select and translate them in the modifier
    $this->select->apply_aliases_to_modifier( $modifier );

    // restrict to this opal_view's files
    $modifier->join( 'opal_view', 'stage_type.opal_view_id', 'opal_view.id', '', NULL, true );
    $modifier->where( 'opal_view.platform_id', '=', $db_platform->id );

    return $stage_type_class_name::select( $this->select, $modifier );
  }
}
