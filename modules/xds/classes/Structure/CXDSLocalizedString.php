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
 * Classe utilisé par Name
 */
class CXDSLocalizedString implements IShortNameAutoloadable {

  public $value;
  public $charset;
  public $lang;

  /**
   * Construction de l'instance
   *
   * @param String $value String
   */
  function __construct($value) {
    $this->value = $value;
    $this->charset = "UTF8";
    $this->lang = "FR";
  }

  /**
   * Génération du xml
   *
   * @return CXDSXmlDocument
   */
  function toXML() {
    $xml = new CXDSXmlDocument();
    $xml->createLocalized($this->value, $this->charset, $this->lang);

    return $xml;
  }
}
