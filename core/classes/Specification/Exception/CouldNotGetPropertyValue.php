<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Specification\Exception;

use Ox\Core\CMbException;
use ReflectionException;

/**
 * Description
 */
class CouldNotGetPropertyValue extends CMbException {
  /**
   * @return self
   */
  public static function isNotAnObject(): self {
    return new self('CouldNotGetPropertyValue-error-Candidate is not an object');
  }

  /**
   * @param mixed               $candidate
   * @param ReflectionException $e
   *
   * @return self
   */
  public static function classNotFoundForReflexion($candidate, ReflectionException $e): self {
    return new self(
      'CouldNotGetPropertyValue-error-%s candidate class not found during reflexion. Error: %s',
      $candidate,
      $e->getMessage()
    );
  }
}
