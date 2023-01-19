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
 * classe correspondant à l?élément XML rim:VersionInfo,
 * contenant la version de l?objet du registre, notamment la version de la fiche ;
 */
class CXDSVersionInfo implements IShortNameAutoloadable {

  public $value;

  /**
   * Construction de l'instance
   *
   * @param String $value String
   */
  function __construct($value) {
    $this->value = $value;
  }

  /**
   * Génération du xml
   *
   * @return CXDSXmlDocument
   */
  function toXML() {
    $xml = new CXDSXmlDocument();

    $xml->createVersionInfo($this->value);

    return $xml;
  }
}
