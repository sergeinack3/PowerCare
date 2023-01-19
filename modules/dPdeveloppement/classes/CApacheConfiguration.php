<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;


/**
 * Class CApacheConfiguration
 */
class CApacheConfiguration extends CDashboardConfiguration {

  static $_modsWhitelist = array(
    "mod_deflate",
    "mod_headers",
    "mod_setenvif",
    "mod_alias",
  );

  /**
   * @see parent::init()
   */
  public function init() {
    $this->getApacheMods();
    $this->configuration["serverInformations"]["version"]              = $_SERVER["SERVER_SOFTWARE"];
    $this->configuration["serverInformations"]["allowOverrideDefined"] = getenv("AllowOverrideDefined");
  }

  /**
   * Get the installed apache modules
   * @return void
   */
  private function getApacheMods() {
    $modsList = apache_get_modules();

    foreach (self::$_modsWhitelist as $mod) {
      $apacheMod = new CSimpleConfigurationVariable($mod);
      foreach ($modsList as $file) {
        if ($file === $mod) {
          $apacheMod->exists = true;
        }
      }
      $this->configuration["mods"][] = $apacheMod;
    }
  }

  /**
   * @see parent::fromJson($jsonData)
   */
  public static function fromJson($jsonData) {
    //Init an empty object
    $apacheConfiguration = new CApacheConfiguration();

    $jsonMods = $jsonData["configuration"]["mods"];

    //Importing mods
    foreach ($jsonMods as $jsonMod) {
      $mod = new CSimpleConfigurationVariable($jsonMod["varName"]);
      $mod->exists = $jsonMod["exists"];

      $apacheConfiguration->configuration["mods"][] = $mod;
    }

    $apacheVersion        = $jsonData["configuration"]["serverInformations"]["version"];
    $allowOverrideDefined = $jsonData["configuration"]["serverInformations"]["allowOverrideDefined"];

    //Importing global apache2 vars
    $apacheConfiguration->configuration["serverInformations"]["version"]              = $apacheVersion;
    $apacheConfiguration->configuration["serverInformations"]["allowOverrideDefined"] = $allowOverrideDefined;

    return $apacheConfiguration;
  }
}