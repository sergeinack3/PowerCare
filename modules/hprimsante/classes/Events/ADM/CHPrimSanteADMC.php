<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante\Events\ADM;

/**
 * Class CHPrimSanteADMC
 * Transfert de données d'admission - Liaisons entre laboratoires et établissements cliniques ou hospitaliers
 */
class CHPrimSanteADMC extends CHPrimSanteADM {
  /**
   * @see parent::__construct
   */
  function __construct() {
    $this->type_liaison = "C";

    parent::__construct();
  }
}