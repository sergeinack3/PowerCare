<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21\Events\ADM;

/**
 * Class CHPrim21ADML
 * Transfert de données d'admission - Liaisons entre laboratoires
 */
class CHPrim21ADML extends CHPrim21ADM {
  function __construct() {
    $this->type_liaison = "L";
    
    parent::__construct();
  }
}

