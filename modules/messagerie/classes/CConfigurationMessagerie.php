<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

class CConfigurationMessagerie extends AbstractConfigurationRegister {

  /**
   * @return mixed
   */
  public function register() {
    CConfiguration::register(
      array(
        'CGroups' => array(
          'messagerie' => array(
            'access' => array(
              'allow_internal_mail'             => 'bool default|1',
              'allow_external_mail'             => 'bool default|1',
              'external_access'                 => 'bool default|1',
              'ldap_directory'                  => 'bool default|0',
              'internal_mail_refresh_frequency' => 'num default|30',
            ),
            "messagerie_interne" => array(
              "resctriction_level_messages"     => "enum list|all|group|function default|all localize",
            ),
            "messagerie_externe" => array(
              "limit_external_mail"       => "num default|20 min|0",
              'retry_number'              => "num default|5 min|0",
              'retrieve_files_on_update'  => 'bool default|0'
            ),
          )
        )
      )
    );
  }
}