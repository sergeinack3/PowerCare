<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Interop\Xds\CXDSXmlDocument;

/**
 * Classe correspondant à l'élément XML rim:Description,
 * contenant la description textuelle de l'objet du registre ;
 */
class CXDSDescription implements IShortNameAutoloadable {
  public $name;
  /** @var CXDSLocalizedString  */
  public $value;

  /**
   * Construction de la classe
   *
   * @param String $value String
   */
  function __construct($value) {
    $this->name = "Description";
    $this->value = new CXDSLocalizedString($value);
  }

  /**
   * Génération du xml de l'instance en cours
   *
   * @return CXDSXmlDocument
   */
  function toXML() {
    $xml = new CXDSXmlDocument();
    $xml->createNameDescriptionRoot($this->name);
    $xml->importDOMDocument($xml->documentElement, $this->value->toXML());

    return $xml;
  }
}
