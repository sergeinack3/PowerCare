<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Webservices;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

class CConfigurationWebservices extends AbstractConfigurationRegister {

  /**
   * @return mixed
   */
  public function register() {
    CConfiguration::register(
      array(
        "CGroups" => array(
          "webservices" => array(
            "CSoapHandler" => array(
              'loggable' => 'bool default|1'
            )
          )
        )
      )
    );
  }
}
