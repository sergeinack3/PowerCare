<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Webservices\Wsdl;

use Ox\Core\CMbException;
use Ox\Core\CWSDL;

/**
 * WSDLRepository interface
 */
interface WSDLRepositoryInterface {
  /**
   * Find a WSDL on a persistence layer
   *
   * @param string|null $login     Login
   * @param string|null $token     Token
   * @param string      $module    Module name
   * @param string      $tab       Tab name
   * @param string      $classname Class name
   * @param string      $wsdl_mode The WSDL mode (CWSDLRPCEncoded or CWSDLRPCLiteral)
   *
   * @return CWSDL|null
   * @throws CMbException
   */
  public function find(?string $login, ?string $token, string $module, string $tab, string $classname, string $wsdl_mode): ?CWSDL;

  /**
   * Save a WSDL to a persistence layer
   *
   * @param CWSDL $wsdl
   *
   * @return bool
   * @throws CMbException
   */
  public function save(CWSDL $wsdl);

  /**
   * Delete a WSDL from a persistence layer
   *
   * @param CWSDL $wsdl
   *
   * @return bool
   * @throws CMbException
   */
  public function delete(CWSDL $wsdl);

  /**
   * FLush all the WSDL on a persistence layer
   *
   * @return int
   */
  public function flush();

  /**
   * Count all the WSDL on a persistence layer
   *
   * @return bool
   */
  public function count();
}
