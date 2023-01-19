<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante\Events\ADM;

/**
 * Class CHPrimSanteADML
 * Transfert de données d'admission - Liaisons entre laboratoires
 */
class CHPrimSanteADML extends CHPrimSanteADM {
  /**
   * @see parent::__construct
   */
  function __construct() {
    $this->type_liaison = "L";

    parent::__construct();
  }
}

