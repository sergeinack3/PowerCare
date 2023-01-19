<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use Ox\Core\CAppUI;

class CMediboardConfiguration extends CDashboardConfiguration {
  static $specificVariables = array(
    'object_handlers',
    'index_handlers',
    'eai_handlers',
  );

  static $mediboardVariables = array(
    'root_dir',
    'master_key_filepath',
    'db std dbhost',
    'offline',
    'offline_non_admin',
    'instance_role',
    'mb_id',
    'mb_oid',
    'servers_ip',
    'minify_javascript',
    'minify_css',
    'ref_pays',
    'hide_confidential',
    'readonly',
    'log_datasource_metrics',
    'log_access',
    'access_log_buffer_lifetime',
    'human_long_request_level',
    'bot_long_request_level',
    'shared_memory',
    'shared_memory_distributed',
    'shared_memory_params',
    'session_handler',
    'session_handler_mutex_type',
    'mutex_drivers CMbRedisMutex',
    'mutex_drivers CMbAPCMutex',
    'mutex_drivers CMbFileMutex',
    'mutex_drivers_params CMbRedisMutex',
    'mutex_drivers_params CMbFileMutex',
    'monitorClient redis_idle_threshold',
    'offline_time_start',
    'offline_time_end',
    'purify_text_input',
    'config_db',
    'dataminer_limit',
    'enslaving_active',
    'enslaving_ratio',
  );

  /**
   * @see parent::init()
   */
  public function init() {
    $this->configuration["mbConfig"] = array();

    foreach (self::$mediboardVariables as $_mediboard_variable) {
      $variable_value = CAppUI::conf($_mediboard_variable);

      $configurationVariable = new CSimpleConfigurationVariable($_mediboard_variable);
      $configurationVariable->exists = true;
      if ($variable_value) {
        $configurationVariable->setValue($variable_value);
      }

      $this->configuration["mbConfig"][] = $configurationVariable;
    }

    foreach (self::$specificVariables as $_mediboard_variable) {
      $handler      = CAppUI::conf($_mediboard_variable);

      foreach ($handler as $key => $_handler_parameter) {
        $tokenized_path = $_mediboard_variable . " " . $key;

        $mediboardParameter = new CSimpleConfigurationVariable($tokenized_path, $_handler_parameter);
        $mediboardParameter->exists = true;
        $this->configuration["mbConfig"][] = $mediboardParameter;
      }
    }
  }

  /**
   * Import a set of data inside $this->configuration variable
   *
   * @param array $jsonData Must be the root of the data ($
   *
   * @return CMediboardConfiguration
   */
  public static function fromJson($jsonData) {
    $mediboard_configuration = new CMediboardConfiguration();

    $imported_config = $jsonData["configuration"]["mbConfig"];

    foreach ($imported_config as $_json_config) {
      $mediboardSetting = new CSimpleConfigurationVariable($_json_config["varName"], $_json_config["value"]);
      $mediboardSetting->exists = $_json_config["exists"];

      $mediboard_configuration->configuration["mbConfig"][] = $mediboardSetting;
    }

    return $mediboard_configuration;
  }
}