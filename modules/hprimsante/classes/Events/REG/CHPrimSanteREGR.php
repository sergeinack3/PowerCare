<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante\Events\REG;

/**
 * Class CHPrimSanteREGR
 * Transfert de données de reglèment - Liaisons entre cabinets de radiologie et établissements cliniques ou hospitaliers
 */
class CHPrimSanteREGR extends CHPrimSanteREG {
  /**
   * @see parent::__construct
   */
  function __construct() {
    $this->type_liaison = "R";

    parent::__construct();
  }
}

