<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

class CShardedObjectSpec extends CMbObjectSpec {
  // Sharders field names
  public $sharders = array();

  /**
   * CShardedObjectSpec constructor
   */
  function __construct() {
    $this->key      = null;
    $this->loggable = false;
  }
}
