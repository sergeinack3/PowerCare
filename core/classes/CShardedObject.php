<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

/**
 * Handles: object stored over multiple table shard, using sharder fields
 *
 * @abstract Sharded stored object layer
 */
class CShardedObject extends CStoredObject {
  public $_shards = null;

  /**
   * @inheritdoc
   *
   * @return CShardedObjectSpec
   */
  function getSpec() {
    return new CShardedObjectSpec();
  }

  /**
   * Build shards, only once
   *
   * @return array() array of name-values specifying the present shards
   */
  function buildShards() {
    if (is_array($this->_shards)) {
      return $this->_shards;
    }

    $this->_shards = array();
    // Seek shards et compute sharders values
    $ds = $this->getDS();

    /** @var CShardedObjectSpec $spec */
    $spec = $this->_spec;

    $sharders_count = count($spec->sharders);
    $table_prefix = $spec->table . "-" . str_repeat("%-", $sharders_count-1);
    $shard_names = $ds->loadTables($table_prefix);
    foreach ($shard_names as $_shard_name) {
      $parts = explode("-", $_shard_name);
      array_shift($parts);
      $shard_values_count = count($parts);
      if ($shard_values_count != $sharders_count) {
        trigger_error("Shard '$_shard_name' has '$shard_values_count' with '$sharders_count' sharders declared", E_USER_WARNING);
      }
      $shard_values = array();
      foreach ($parts as $_i => $_part) {
        $shard_values[$spec->sharders[$_i]] = $_part;
      }
      $this->_shards[] = array(
        "name" => $_shard_name,
        "values" => $shard_values,
      );
    }

    return $this->_shards;
  }

  /**
   * @inheritdoc
   */
  function loadList($where = null, $order = null, $limit = null, $group = null, $ljoin = null, $index = null, $having = null, bool $strict = true, ?int $limit_time = null) {
    if (!$this->_ref_module) {
      return null;
    }

    $this->buildShards();

    // Backup root table
    $table_name = $this->_spec->table;

    $objects = array();
    foreach ($this->_shards as $_shard) {
      // Reroot table
      $shard_table = $_shard["name"];
      $this->_spec->table = "$shard_table";
      $shard_objects = parent::loadList($where, $order, $limit, $group, $ljoin, $index, $having, $strict, $limit_time);
      foreach ($shard_objects as $_shard_object) {
        CMbObject::setProperties($_shard['values'], $_shard_object, false, false);
        $objects[] = $_shard_object;
      }
    }

    // Restore root table
    $this->_spec->table = $table_name;

    return $objects;
  }
}
