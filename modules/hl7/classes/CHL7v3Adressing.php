<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;
use DOMNode;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbSecurity;
use Ox\Interop\Dmp\CDMPRequest;
use Ox\Interop\Dmp\CDMPXmlDocument;
use SoapHeader;
use SoapVar;

/**
 * Class CHL7v3Adressing
 * WSAddressing
 */
class CHL7v3Adressing implements IShortNameAutoloadable {
  /**
   * Création de l'entête
   *
   * @param string $name           The name of the SoapHeader object
   * @param mixed  $data           A SOAP header's content (PHP value)
   * @param bool   $mustunderstand Value of the mustUnderstand attribute of the SOAP header element
   *
   * @return SoapHeader
   */
  static function createHeaders($name, $data, $mustunderstand = false) {
    return new SoapHeader("http://www.w3.org/2005/08/addressing", $name, $data, $mustunderstand);
  }

  /**
   * Création d'une entête WS-Adressing
   *
   * @param string $action_name Action name
   * @param string $to          To
   *
   * @return array
   */
  static function createWSAddressing($action_name, $to, $object_return = false) {
      if (!$object_return) {
          $headers[] = self::createHeaders("Action"   , $action_name, true);
          $headers[] = self::createHeaders("MessageID", "urn:uuid:".CMbSecurity::generateUUID());
          $headers[] = self::createHeaders(
              "ReplyTo",
              new SoapVar('<ns1:ReplyTo><ns1:Address>http://www.w3.org/2005/08/addressing/anonymous</ns1:Address></ns1:ReplyTo>', XSD_ANYXML)
          );
          $headers[] = self::createHeaders("To"       , $to, true);

          return $headers;
      }

      return CDMPRequest::addHeadersWSAddressing($action_name, $to);
  }
}
