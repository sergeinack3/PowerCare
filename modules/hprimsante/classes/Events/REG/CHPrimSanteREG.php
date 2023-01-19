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
 * Transfert de donn�es de regl�ment
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

    /* @todo Pas de cr�ation de message pour le moment */
  }
}

