<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * Class CConfigurationHospi
 */
class CConfigurationHospi extends AbstractConfigurationRegister {

  /**
   * @return mixed
   */
  public function register() {
    CConfiguration::register(
      array(
        "CGroups" => array(
          "dPhospi" => array(
            "General"         => array(
              "tag_service"           => "str default|",
              "pathologies"           => "bool default|0",
              "nb_hours_trans"        => "num default|1",
              "hour_limit"            => "str default|16:00:00",
              "show_age_patient"      => "bool default|0",
              "max_affectations_view" => "num default|480",
              "stats_for_all"         => "bool default|0",
              "show_uf"               => "bool default|1",
            ),
            "CAffectation"    => array(
              "create_affectation_tolerance" => "num min|2 max|120 default|2",
              "sejour_default_affectation"   => "bool default|0",
            ),
            "CLit"            => array(
              "show_in_tableau" => "bool default|0",
              "prefixe"         => "str default|",
              "tag"             => "str default|",
            ),
            "CChambre"        => array(
              "prefixe" => "str default|",
              "tag"     => "str default|"
            ),
            "placement"       => array(
              "alerte_patient_mineur" => "bool default|0",
              "alerte_sexe_opposes"   => "bool default|0",
              "ask_etab_externe"      => "bool default|0"
            ),
            "vue_topologique" => array(
              "use_vue_topologique"         => "bool default|0",
              "nb_colonnes_vue_topologique" => "num default|10",
            ),
            "vue_tableau"     => array(
              "show_labo_results" => "bool default|1",
            ),
            "vue_temporelle"  => array(
              "hide_alertes_temporel"  => "bool default|0",
              "infos_interv"           => "bool default|0",
              "prolongation_ambu"      => "bool default|1",
              "open_fenetre_action"    => "bool default|0",
              "order_changement_lit"   => "enum list|libre_ASC|libre_DESC|alpha|rank default|libre_ASC localize",
              "display_placer_couloir" => "bool default|1",
              "nb_days_prolongation"   => "num default|30",
            ),
            "colors"          => array(
              "comp"    => "color default|fff",
              "ambu"    => "color default|faa",
              "exte"    => "color default|afa",
              "seances" => "color default|68f",
              "ssr"     => "color default|ffcc66",
              "psy"     => "color default|ff66ff",
              "urg"     => "color default|ff6666",
              "consult" => "color default|cfdfff",
              "default" => "color default|cccccc",
              "recuse"  => "color default|ffff66",
              "annule"  => "color default|f88",
            ),
            "prestations"     => array(
              "systeme_prestations"       => "enum list|standard|expert default|standard localize",
              "systeme_prestations_tiers" => "enum list|Aucun|web100T|softway default|Aucun onlyAdmin",
              "expert_colonne"            => "bool default|0",
              "facture_outclass"          => "bool default|0",
              "show_realise"              => "bool default|1",
              "show_souhait_placement"    => "bool default|0",
            ),
            "mouvements"      => array(
              "print_comm_patient_present" => "bool default|0",
              "order_col_default_chambre"  => "bool default|0",
              "show_age_sexe_mvt"          => "bool default|0",
              "show_hour_anesth_mvt"       => "bool default|0",
              "show_retour_mvt"            => "bool default|0",
              "show_collation_mvt"         => "bool default|0",
              "show_sortie_mvt"            => "bool default|0",
              "tag"                        => "str default|"
            ),
            "print_planning"  => array(
              "modele_used" => "enum localize list|standard|modele1 default|standard",
              "col1"        => "enum list|patient|sejour|interv default|sejour localize",
              "col2"        => "enum list|patient|sejour|interv default|interv localize",
              "col3"        => "enum list|patient|sejour|interv default|patient localize"
            ),
            'CInfoGroup'      => array(
              'split_by_users' => 'bool default|0'
            ),
          )
        ),

        "CService CGroups.group_id" => array(
          "dPhospi" => array(
            "vue_temporelle" => array(
              "hour_debut_day"   => "num min|0 max|23 default|0",
              "hour_fin_day"     => "num min|0 max|23 default|23",
              "show_imc_patient" => "bool default|0",
            ),
          )
        ),
      )
    );
  }
}
