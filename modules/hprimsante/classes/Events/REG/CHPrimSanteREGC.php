<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante\Events\REG;

/**
 * Class CHPrimSanteREGC
 * Transfert de données de reglèment - Liaisons entre laboratoires et établissements cliniques ou hospitaliers
 */
class CHPrimSanteREGC extends CHPrimSanteREG {
  /**
   * @see parent::__construct
   */
  function __construct() {
    $this->type_liaison = "C";

    parent::__construct();
  }
}

