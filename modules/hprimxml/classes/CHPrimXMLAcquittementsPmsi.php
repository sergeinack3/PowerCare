<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Ox\Core\CAppUI;

/**
 * Class CHPrimXMLAcquittementsPmsi
 */
class CHPrimXMLAcquittementsPmsi extends CHPrimXMLAcquittementsServeurActivitePmsi {
  /**
   * @see parent::__construct
   */
  function __construct() {
    $this->evenement    = "evt_pmsi";
    $this->acquittement = "acquittementsPmsi";

    $version = CAppUI::conf("hprimxml $this->evenement version");

    parent::__construct(
      "serveurActivitePMSI/v".str_replace(".", "_", $version),
      "msgAcquittementsPmsi".str_replace(".", "", $version)
    );
  }
}