<?php
/**
 * @package Mediboard\Reservation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Reservation;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

class CConfigurationReservation extends AbstractConfigurationRegister {

  /**
   * @return mixed
   */
  public function register() {
    CConfiguration::register(
      array(
        "CGroups" => array(
          "reservation" => array(
            "General" => array(
              "prolongation_current_interv" => "bool default|0"
            )
          )
        )
      )
    );
  }
}