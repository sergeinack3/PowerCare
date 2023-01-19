<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sante400;

use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

class CConfigurationSante400 extends AbstractConfigurationRegister {

  /**
   * @return mixed
   */
  public function register() {
    $types = array();
    foreach (CSejour::$types as $_type) {
      $types[$_type] = "str default|$_type";
    }

    CConfiguration::register(
      array(
        //"CService CGroups.group_id"
        "CGroups" => array(
          "dPsante400" => array(
            "CIdSante400"  => array(
              "add_ipp_nda_manually"     => "bool default|0",
              "admit_ipp_nda_obligatory" => "bool default|0",
            ),
            "CIncrementer" => array(
              "type_sejour" => $types,
              "CSejour"     => array(
                "increment_NDA_date_min" => "date"
              )
            ),
            "CDomain"      => array(
              "group_id_pour_sejour_facturable" => "num",
            )
          ),
        ),
      )
    );
  }
}