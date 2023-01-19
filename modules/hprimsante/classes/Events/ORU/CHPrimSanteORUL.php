<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante\Events\ORU;

/**
 * Class CHPrimSanteORUL
 * Transmission du résultat d'un test - Liaisons entre laboratoires
 */
class CHPrimSanteORUL extends CHPrimSanteORU {
  /**
   * @see parent::__construct
   */
  function __construct() {
    $this->type_liaison = "L";

    parent::__construct();
  }
}

