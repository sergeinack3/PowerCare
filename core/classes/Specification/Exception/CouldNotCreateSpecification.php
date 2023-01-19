<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Specification\Exception;

use Ox\Core\CMbException;

/**
 * Description
 */
class CouldNotCreateSpecification extends CMbException {
  /**
   * @return self
   */
  public static function invalidParameters(): self {
    return new self('CouldNotCreateSpecification-error-Invalid parameter|pl');
  }

  /**
   * @return self
   */
  public static function missingParameters(): self {
    return new self('CouldNotCreateSpecification-error-Missing parameter|pl');
  }

  /**
   * @return self
   */
  public static function noPropertyParameter(): self {
    return new self('CouldNotCreateSpecification-error-First parameter must be a property name');
  }
}
