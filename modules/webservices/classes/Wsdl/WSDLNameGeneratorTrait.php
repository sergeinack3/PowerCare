<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Webservices\Wsdl;

/**
 * WSDL name generator
 */
trait WSDLNameGeneratorTrait {
  /**
   * Generates a WSDL name according to given parameters
   *
   * @param string|null $login     Login
   * @param string|null $token     Token
   * @param string      $module    Module name
   * @param string      $tab       Tab name
   * @param string      $classname Class name
   *
   * @return string
   */
  static private function generateWSDLName(?string $login, ?string $token, string $module, string $tab, string $classname): string {
    $filename = $token;

    // login with "login=user:password"
    if (strpos($login ?? '', ':') !== false) {
      list($filename,) = explode(':', $login, 2);
    }

    return md5("{$module}_{$tab}_{$classname}_{$filename}");
  }
}
