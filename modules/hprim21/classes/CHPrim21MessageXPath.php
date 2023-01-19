<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21;

use DOMDocument;
use Ox\Core\CMbXPath;

/**
 * Class CHPrim21MessageXPath 
 * XPath HPR
 */
class CHPrim21MessageXPath extends CMbXPath {
  function __construct(DOMDocument $dom) {
    parent::__construct($dom);
    
    $this->registerNamespace("hpr", "urn:hpr-org:v2xml");
  }
  
  function convertEncoding($value) {
    return $value;
  }
}
