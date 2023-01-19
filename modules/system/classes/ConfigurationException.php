<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\CMbException;

/**
 * Description
 */
class ConfigurationException extends CMbException {
  /**
   * @param string $module
   *
   * @return self
   */
  public static function moduleDoesNotHaveStaticConfiguration(string $module): self {
    return new self("ConfigurationManager-error-Module '%s' does not have static configuration", $module);
  }

  /**
   * @param string $module
   *
   * @return self
   */
  public static function moduleAlreadyHasRegisteredStaticConfigurations(string $module): self {
    return new self("ConfigurationManager-error-Module '%s' has already registered static configurations", $module);
  }

  /**
   * @param string $key
   *
   * @return self
   */
  public static function invalidParameter(string $key): self {
    return new self("ConfigurationManager-error-The provided key is not in a valid format: '%s'", $key);
  }
}
