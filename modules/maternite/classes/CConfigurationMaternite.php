<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite;

use Ox\Core\Handlers\HandlerParameterBag;
use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

class CConfigurationMaternite extends AbstractConfigurationRegister {
  /**
   * @inheritDoc
   */
  public function register() {
    CConfiguration::register(
      array(
        "CGroups" => array(
          "maternite" => array(
            "CGrossesse" => array(
              "min_check_terme"         => "num default|7",
              "max_check_terme"         => "num default|21",
              "lock_partogramme"        => "bool default|0",
              "date_regles_obligatoire" => "bool default|0",
              "manage_provisoire"       => "bool default|1",
              "audipog"                 => "bool default|0",
            ),
            "CNaissance" => array(
              "num_naissance"       => "num default|1",
              "now_naissance_provi" => "bool default|0",
              "uf_medicale_id"      => "custom tpl|inc_config_uf_medicale",
              "elt_oea"             => "custom tpl|inc_config_elt_oea",
              "elt_guthrie"         => "custom tpl|inc_config_elt_guthrie",
            ),
            "placement"  => array(
              "charge_id_dhe"   => "custom tpl|inc_config_charge",
              "uf_soins_id_dhe" => "custom tpl|inc_config_uf_soins",
            ),
            "general"    => array(
              "vue_alternative" => "bool default|0",
              "map_sortie"      => "bool default|0",
              "days_terme"      => "num min|0 default|5",
              "duree_sejour"    => "num min|0 default|3",
            ),
          ),
        ),
      )
    );
  }

  /**
   * @inheritDoc
   */
  public function getObjectHandlers(HandlerParameterBag $parameter_bag): void {
      $parameter_bag
          ->register(CAffectationHandler::class, false);
  }
}
