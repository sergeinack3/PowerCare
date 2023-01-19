<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admissions;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * Class CConfigurationAdmissions
 */
class CConfigurationAdmissions extends AbstractConfigurationRegister {

  /**
   * @return mixed
   */
  public function register() {
    CConfiguration::register(
      array(
        "CGroups" => array(
          "dPadmissions" => array(
            "General"          => array(
              "view_sortie_masss"     => "bool default|0",
              "use_perms"             => "bool default|0",
              "hour_matin_soir"       => "time default|12:00:00",
              "show_curr_affectation" => "bool default|0",
              "show_deficience"       => "bool default|0",
              "pagination_step"       => "enum list|50|100|150|200|250|300|350|400|450|500 default|50",
            ),
            "admission"        => array(
              "provenance_transfert_obligatory"   => "bool default|0",
              "provenance_mutation_obligatory"    => "bool default|0",
              "date_entree_transfert_obligatory"  => "bool default|0",
              "etab_externe_transfert_obligatory" => "bool default|0",
            ),
            "presents"         => array(
              "see_prestation" => "bool default|1",
            ),
            "sortie"           => array(
              "etab_externe_transfert_obligatory" => "bool default|0",
              "show_prestations_sorties"          => "bool default|0",
            ),
            "presentation"     => array(
              "rafraichissement_pages"     => "enum list|60|120|300|600 default|60 localize",
              "vitesse_defilement_pages"   => "enum list|15|30|45 default|15 localize",
              "rafraichissement_bandeau"   => "enum list|60|120|300|600 default|60 localize",
              "vitesse_defilement_bandeau" => "enum list|5|10|30 default|10 localize",
              "nb_elements_affiche"        => "enum list|5|10|15|20 default|10",
              "sound_alert"                => "bool default|1",
            ),
            "automatic_reload" => array(
              "auto_refresh_frequency_identito"      => 'enum list|90|180|300|600 default|90 localize',
              'auto_refresh_frequency_admissions'    => 'enum list|120|300|600|never default|120 localize',
              'auto_refresh_frequency_sorties'       => 'enum list|120|300|600|never default|120 localize',
              'auto_refresh_frequency_preadmissions' => 'enum list|120|300|600|never default|120 localize',
              'auto_refresh_frequency_permissions'   => 'enum list|120|300|600|never default|120 localize',
              'auto_refresh_frequency_presents'      => 'enum list|120|300|600|never default|120 localize'
            )
          )
        )
      )
    );
  }
}

