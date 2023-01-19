<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Specification;

/**
 * Description
 */
interface ParameterizedSpecificationInterface {
  /**
   * Set the parameters of a specification
   *
   * @param mixed ...$params
   *
   * @return $this
   */
  public function setParameters(...$params): ParameterizedSpecificationInterface;
}