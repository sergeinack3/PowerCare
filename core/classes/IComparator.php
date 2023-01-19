<?php
/**
 * @package Mediboard\Ox\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

/**
 * Description
 */
interface IComparator {
  /**
   * @param mixed $a
   * @param mixed $b
   *
   * @return bool
   * @throws ComparatorException
   */
  public function equals($a, $b);
}