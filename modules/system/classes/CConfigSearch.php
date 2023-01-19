<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\Module\CModule;

/**
 * Configuration search utility class
 */
class CConfigSearch implements IShortNameAutoloadable {
  const TYPE_CONFIG_INSTANCE = 1;
  const TYPE_CONFIG_ETAB = 2;
  const TYPE_CONFIG_PREF = 3;
  const TYPE_CONFIG_FUNC_PERM = 4;
  const TYPE_CONFIG_SERVICE = 5;

  /**
   * Get the list of all configs (instance and object related configs) as a flat array
   *
   * @return array
   */
  static function getConfigs() {
    global $dPconfig;

    $config_save = $dPconfig;

    include __DIR__ . "/../../../includes/config_dist.php";

    // Module config file inclusion
//    $config_files = glob(__DIR__ . "/../../*/config.php");
////    foreach ($config_files as $file) {
////      include $file;
////    }

    $configs = $dPconfig;
    $configs = array_fill_keys(array_keys(self::flatten("", $configs)), self::TYPE_CONFIG_INSTANCE);

    $dPconfig = $config_save;

    $configs_etab = array();

    $model   = array();
    $modules = CModule::getInstalled();

    foreach ($modules as $_mod) {
      $model = array_merge_recursive($model, CConfigurationModelManager::_getModel($_mod->mod_name));
    }

    foreach ($model as $_inherit => $_configs) {
      $configs_etab = array_merge($configs_etab, $_configs);
    }

    $configs_etab = array_fill_keys(array_keys($configs_etab), self::TYPE_CONFIG_ETAB);

    $configs = array_merge($configs, $configs_etab);

    return $configs;
  }

  /**
   * Flatten a tree array
   *
   * @param string $prefix Prefix to glue elements of the tree
   * @param array  $array  The tree to flatten
   *
   * @return array
   */
  static function flatten($prefix, $array) {
    $result = array();
    foreach ($array as $key => $value) {
      if (in_array($key, array("db", "php", "ft"))) {
        continue;
      }

      if (is_array($value)) {
        $result = array_merge($result, self::flatten("$prefix$key ", $value));
      }
      else {
        $result[$prefix . $key] = true;
      }
    }

    return $result;
  }
}
