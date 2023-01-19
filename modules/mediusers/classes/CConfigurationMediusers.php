<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

class CConfigurationMediusers extends AbstractConfigurationRegister {

  /**
   * @return mixed
   */
  public function register() {
    CConfiguration::register(
      array(
        'CGroups' => array(
          'mediusers' => array(
            'CMediusers' => array(
              'force_professional_context' => 'bool default|0',
              "tag_mediuser"               => "str default|",
              "tag_mediuser_software"      => "str default|",
            ),
          ),
        )
      )
    );
  }
}