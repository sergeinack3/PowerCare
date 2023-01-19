<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante\Events\ADM;

use Ox\Interop\Hprimsante\Events\CHPrimSanteEvent;
use Ox\Mediboard\Patients\CPatient;

/**
 * Class CHPrimSanteADM
 * Transfert de données d'admission
 */
class CHPrimSanteADM extends CHPrimSanteEvent {
  /**
   * construct
   */
  function __construct() {
    $this->type = "ADM";
  }

  /**
   * @see parent::build()
   */
  function build($object) {
    parent::build($object);

    if ($object instanceof CPatient) {
      $patient = $object;
    }
    else {
      $patient = $object->loadRefPatient();
    }

    $this->addP($patient);

    $this->addL();
  }
}

