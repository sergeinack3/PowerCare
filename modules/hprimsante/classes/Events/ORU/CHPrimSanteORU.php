<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante\Events\ORU;

use Ox\Interop\Hprimsante\Events\CHPrimSanteEvent;

/**
 * Class CHPrimSanteORU
 * Transmission du résultat d'un test
 */
class CHPrimSanteORU extends CHPrimSanteEvent {
  /**
   * Construct
   */
  function __construct() {
    $this->type = "ORU";
  }

  /**
   * @see parent::build()
   */
  function build($object) {
    parent::build($object);

    /* @todo Pas de création de message pour le moment */
  }
}

