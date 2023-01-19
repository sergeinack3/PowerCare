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
 * classe correspondant � l?�l�ment XML rim:Slot, contenant l?insertion
 * d?une liste variable d?attributs suppl�mentaires � un objet du registre ;
 */
class CXDSSlot implements IShortNameAutoloadable {

  public $name;
  public $data = array();

  /**
   * Cr�ation d'une instance
   *
   * @param String   $name String
   * @param String[] $data String[]
   */
  function __construct($name, $data) {
    $this->name = $name;
    $this->data = $data;
  }

  /**
   * G�n�ration de xml
   *
   * @return CXDSXmlDocument
   */
  function toXML() {
    $xml = new CXDSXmlDocument();
    $xml->createSlotRoot($this->name);
    $xml->createSlotValue($this->data);

    return $xml;
  }
}
