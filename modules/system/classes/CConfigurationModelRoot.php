<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use SplObjectStorage;

/**
 * Root node of CConfiguration model
 */
class CConfigurationModelRoot extends CConfigurationModel {
  /** @var SplObjectStorage Children nodes */
  protected $children;

  /**
   * @inheritdoc
   */
  protected function init() {
    parent::init();

    $this->children = new SplObjectStorage();
  }

  /**
   * Add unique child to root
   *
   * @param CConfigurationModel $child Child node
   *
   * @return void
   */
  public function addChild(CConfigurationModel $child) {
    $this->getChildren()->attach($child);
  }

  /**
   * CConfigurationModelLeaf accessor
   *
   * @return SplObjectStorage
   */
  public function getChildren() {
    return $this->children;
  }
}
