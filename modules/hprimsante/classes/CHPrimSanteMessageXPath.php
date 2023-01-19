<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante;

use DOMDocument;
use Ox\Core\CMbXPath;

/**
 * Class CHPrimSanteMessageXPath
 * XPath HPR
 */
class CHPrimSanteMessageXPath extends CMbXPath {
  /**
   * @see parent::__construct()
   */
  function __construct(DOMDocument $dom) {
    parent::__construct($dom);

    $this->registerNamespace("hpr", "urn:hpr-org:v2xml");
  }

  /**
   * @see parent::convertEncoding
   */
  function convertEncoding($value) {
    return $value;
  }
}
