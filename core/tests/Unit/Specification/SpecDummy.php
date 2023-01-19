<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Specification;

/**
 * Description
 */
final class SpecDummy {
  public $public_property;

  protected $protected_property;

  private $private_property;

  /**
   * @param mixed|null $value
   *
   * @return void
   */
  public function setProtectedProperty($value): void {
    $this->protected_property = $value;
  }

  /**
   * @param mixed|null $value
   *
   * @return void
   */
  public function setPrivateProperty($value): void {
    $this->private_property = $value;
  }

  /**
   * @return string
   */
  public function __toString(): string {
    return 'SpecDummy';
  }
}
