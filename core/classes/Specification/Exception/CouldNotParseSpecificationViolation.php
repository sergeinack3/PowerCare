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
class CouldNotParseSpecificationViolation extends CMbException {
  /**
   * @param mixed $specification_class
   *
   * @return self
   */
  public static function unknownProvidedComposite($specification_class): self {
    return new self('CouldNotParseSpecificationViolation-error-Unknown provided Composite Specification %s', $specification_class);
  }
}
