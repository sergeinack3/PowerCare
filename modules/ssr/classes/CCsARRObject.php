<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\CStoredObject;

/**
 * Object CsARR
 */
class CCsARRObject extends CStoredObject {

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec              = parent::getSpec();
    $spec->dsn         = 'csarr';
    $spec->incremented = false;

    return $spec;
  }

  /**
   * Return the current version and the next available version of the CsARR database
   *
   * @return array (string current version, string next version)
   */
  public static function getDatabaseVersions() {
    return array(
      "< v2015" => array(
        array(
          "table_name" => "activite",
          "filters"    => array()
        )
      ),
      "v2015"   => array(
        array(
          "table_name" => "activite",
          "filters"    => array(
            "code" => "= 'AGR+102'"
          )
        )
      ),
      "v2016"   => array(
        array(
          "table_name" => "activite",
          "filters"    => array(
            "code" => "= 'ZZM+193'"
          )
        )
      ),
      "v2017"   => array(
        array(
          "table_name" => "activite",
          "filters"    => array(
            "code" => "= 'PEB+196'"
          )
        )
      ),
      "v2018"   => array(
        array(
          "table_name" => "activite",
          "filters"    => array(
            "code"    => "= 'PBR+256'",
            "libelle" => "LIKE '%ance de mobilisation articulaire passive'"
          )
        )
      ),
      "v2019"   => array(
        array(
          "table_name" => "activite",
          "filters"    => array(
            "code" => "= 'ZFR+031'"
          )
        )
      ),
      "v2020"   => array(
        array(
          "table_name" => "activite",
          "filters"    => array(
            "code" => "= 'ZZQ+085'",
            "libelle" => "= 'Évaluation des capacités sensitives et motrices nécessaires pour la conduite d\'un véhicule automobile sans adaptation personnalisée'"
          )
        )
      ),

    );
  }
}
