<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sante400;

// Mouvement factory

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;

class CMouvFactory implements IShortNameAutoloadable {
  /**
   * @todo Refactor to enable module extendability with definition files
   */
  static $modes = array(
    "default" => array(),
    "sample"  => array(
      "patient" => "CMouvMedecinPatient",
    ),
    "medicap" => array(
      "intervention" => "CMouvECapIntervention",
      "attendue"     => "CMouvECapAttendue",
      "patient"      => "CMouvECapPatient",
    ),
  );

  /**
   * Get available types for current compat config
   *
   * @return array the types, null for invalid compat
   */
  static function getTypes() {
    if (null == $mode_compat = CAppUI::conf("interop mode_compat")) {
      trigger_error("Mode de compatibilité non initalisé", E_USER_ERROR);

      return;
    }

    if (!array_key_exists($mode_compat, self::$modes)) {
      trigger_error("Mode de compatibilité '$mode_compat' non géré", E_USER_ERROR);

      return;
    }

    return array_keys(self::$modes[$mode_compat]);
  }

  /**
   * Get available types for current compat config
   *
   * @return array the types, null for invalid compat
   */
  static function getClasses() {
    if (null == $mode_compat = CAppUI::conf("interop mode_compat")) {
      trigger_error("Mode de compatibilité non initalisé", E_USER_ERROR);

      return;
    }

    if (!array_key_exists($mode_compat, self::$modes)) {
      trigger_error("Mode de compatibilité '$mode_compat' non géré", E_USER_ERROR);

      return;
    }

    return array_values(self::$modes[$mode_compat]);
  }

  /**
   * Create a mouvement instance from given type
   *
   * @param string $type
   *
   * @return void|CMouvement400 concrete instance, null for unhandled type
   */
  static function create($type) {
    $mode_compat = CAppUI::conf("interop mode_compat");
    if (null == $class = @self::$modes[$mode_compat][$type]) {
      trigger_error("Pas de gestionnaire en mode de compatibilité '$mode_compat' et type de mouvement '$type'", E_USER_ERROR);

      return;
    }

    return new $class;
  }
}
