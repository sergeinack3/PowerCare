<?php
/**
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Personnel;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * Class CConfigurationPersonnel
 */
class CConfigurationPersonnel extends AbstractConfigurationRegister {

  /**
   * @return mixed
   */
  public function register() {
    CConfiguration::register(
      array(
        "CGroups" => array(
          "personnel" => array(
            "global"        => array(
              "see_retrocession" => "bool default|0",
            ),
            "CRemplacement" => array(
              "duree_max" => "num default|12",
            ),
            "CPlageConge"   => array(
              "show_replacer" => "bool default|1",
            ),
          )
        )
      )
    );
  }
}
