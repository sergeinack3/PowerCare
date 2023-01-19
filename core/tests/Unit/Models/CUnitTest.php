<?php
/**
 * @package Mediboard\Core\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Models;


/**
 * Class CUnitTest
 * @package Ox\Core\Tests\Unit\Controllers
 */
class CUnitTest {
  private const PRIVATE_CONST = 'PRIVATE';
  public const PUBLIC_CONST = 'PUBLIC';

  /**
   * @param array|null $args
   * @param string     $other_args
   *
   * @return array|string|null
   */
  private function privateMethod(array $args = null, $other_args = null) {

    if ($other_args) {
      return $other_args;
    }

    if ($args) {
      return $args;
    }

    return 'default';
  }

  /**
   * @return bool
   */
  private static function privateStaticMethod(): bool {
    return true;
  }


}