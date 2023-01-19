<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\CRequest;

/**
 * Leaf node of CConfiguration model
 */
class CConfigurationModelLeaf extends CConfigurationModel {
  /** @var string Field used as forward ref */
  protected $fwd_field;

  /**
   * Forward field setter
   *
   * @param string $fwd Forward field
   *
   * @return static
   */
  public function setFwdField($fwd) {
    $this->fwd_field = $fwd;

    return $this;
  }

  /**
   * Forward field accessor
   *
   * @return string
   */
  public function getFwdField() {
    return $this->fwd_field;
  }

  /**
   * @inheritdoc
   *
   * @param array $foreign_keys Foreign keys
   */
  public function loadObjectIDs($foreign_keys = array()) {
    $_class = $this->getContextClass();

    $_obj = new $_class();
    $_ds  = $_obj->getDS();

    $table = $_obj->getSpec()->table;
    if (!$_ds->hasTable($table) || !$_ds->hasField($table, $this->getFwdField())) {
      return;
    }

    $_request = new CRequest();
    $_request->addSelect(array("{$_obj->_spec->key} AS id", "{$this->getFwdField()} as parent_id"));
    $_request->addTable($_obj->_spec->table);

    if ($foreign_keys) {
      $_request->addWhere(
        array(
          $this->getFwdField() => $_ds::prepareIn($foreign_keys),
        )
      );
    }

    $this->setObjectIDs($_ds->loadList($_request->makeSelect()));
  }
}
