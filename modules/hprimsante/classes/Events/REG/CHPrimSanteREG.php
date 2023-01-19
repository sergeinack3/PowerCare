<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante\Events\REG;

use Ox\Interop\Hprimsante\Events\CHPrimSanteEvent;

/**
 * Class CHPrimSanteREG
 * Transfert de données de reglèment
 */
class CHPrimSanteREG extends CHPrimSanteEvent {
  /**
   * construct
   */
  function __construct() {
    $this->type = "REG";
  }

  /**
   * @see parent::build()
   */
  function build($object) {
    parent::build($object);

    /* @todo Pas de création de message pour le moment */
  }
}

