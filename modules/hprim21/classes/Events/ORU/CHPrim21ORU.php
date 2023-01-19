<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21\Events\ORU;

use Ox\Interop\Hprim21\Events\CHPREvent;

/**
 * Class CHPrim21ORU
 * Transfert de données de reglèment
 */
class CHPrim21ORU extends CHPREvent {
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

