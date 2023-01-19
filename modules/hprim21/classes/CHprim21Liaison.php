<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21;

use Ox\Core\Autoload\IShortNameAutoloadable;

/**
 * The HPRIM 2.1 liaison class
 */

class CHprim21Liaison implements IShortNameAutoloadable {
  static $evenements = array(
    "ADM" => "", 
    "ERR" => "",
    "FAC" => "",
    "ORM" => "", 
    "ORU" => "",
    "REG" => "" 
  );
  
}

