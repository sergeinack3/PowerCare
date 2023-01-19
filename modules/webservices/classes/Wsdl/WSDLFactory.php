<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Webservices\Wsdl;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbException;
use Ox\Core\CWSDL;

/**
 * WSDL factory
 */
class WSDLFactory implements IShortNameAutoloadable {
  use WSDLNameGeneratorTrait;

  /**
   * Create a WSDL object
   *
   * @param string|null $login     Login
   * @param string|null $token     Token
   * @param string      $module    Module name
   * @param string      $tab       Tab name
   * @param string      $classname Class name
   * @param string      $wsdl_mode The WSDL mode (CWSDLRPCEncoded or CWSDLRPCLiteral)
   *
   * @return CWSDL
   * @throws CMbException
   */
  static public function create(?string $login, ?string $token, string $module, string $tab, string $classname, string $wsdl_mode): CWSDL {
    $wsdl = new $wsdl_mode();

    if (!$wsdl instanceof CWSDL) {
      throw new CMbException("WSDLFactory-error-WSDL mode '%s' is not valid", $wsdl_mode);
    }

    $wsdl->setName(static::generateWSDLName($login, $token, $module, $tab, $classname));

    // Pour garder en référence les fonctions a decrire
    $wsdl->_soap_handler = new $classname();
    $wsdl->addTypes();
    $wsdl->addMessage();
    $wsdl->addPortType();
    $wsdl->addBinding();
    $wsdl->addService($login, $token, $module, $tab, $classname, $wsdl_mode);

    return $wsdl;
  }

  /**
   * Create a WSDL object from XML document
   *
   * @param string $wsdl_mode The WSDL mode (CWSDLRPCEncoded or CWSDLRPCLiteral)
   * @param string $classname Class name
   * @param string $name      WSDL name
   * @param string $xml       WSDL XML content
   *
   * @return CWSDL
   * @throws CMbException
   */
  static public function createFromString(string $wsdl_mode, string $classname, string $name, string $xml): CWSDL {
    $wsdl = new $wsdl_mode();

    if (!$wsdl instanceof CWSDL) {
      throw new CMbException("WSDLFactory-error-WSDL mode '%s' is not valid", $wsdl_mode);
    }

    try {
      if ($wsdl->loadXML($xml) === false) {
        throw new CMbException('WSDLFactory-error-Unable to load WSDL XML content');
      }
    }
    catch (CMbException $e) {
      throw $e;
    }
    catch (Exception $e) {
      throw new CMbException($e->getMessage());
    }

    $wsdl->setName($name);
    $wsdl->_soap_handler = new $classname();

    return $wsdl;
  }
}
