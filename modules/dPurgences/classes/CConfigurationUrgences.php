<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

class CConfigurationUrgences extends AbstractConfigurationRegister
{

    /**
     * @return mixed
     */
    public function register()
    {
        CConfiguration::register(
            [
                "CGroups" => [
                    "dPurgences" => [
                        "CRPU"          => [
                            "gestion_degre_urgence"           => "bool default|0",
                            "impose_degre_urgence"            => "bool default|0",
                            "impose_diag_infirmier"           => "bool default|0",
                            "impose_motif"                    => "bool default|0",
                            "impose_ide_referent"             => "bool default|0",
                            "impose_create_sejour_mutation"   => "bool default|0",
                            "provenance_domicile_pec_non_org" => "bool default|0",
                            "gestion_motif_sfmu"              => "enum list|0|1|2 default|1 localize",
                            "motif_sfmu_accueil"              => "bool default|0",
                            "cimu_accueil"                    => "bool default|0",
                            "provenance_necessary"            => "bool default|0",
                            "required_from_when_transfert"    => "bool default|0",
                            "imagerie_etendue"                => "bool default|0",
                            "display_motif_sfmu"              => "bool default|0",
                            "defer_sfmu_diag_inf"             => "bool default|0",
                            "diag_prat_view"                  => "bool default|0",
                            "search_visit_days_count"         => "num default|1 min|0 max|15",
                            "impose_lit_service_mutation"     => "bool default|0",
                            "use_session_responsable"         => "bool default|0",
                            "lock_change_ccmu_in_box"         => "bool default|0",
                            "mode_entree_provenance_mutation" => "enum list|0|1 default|0 localize",
                            "gerer_hospi"                     => "bool default|1",
                            "show_fields_sortie_rpu"          => "bool default|1",
                            "uf_soins_UHCD"                   => "custom tpl|inc_config_uf_soins",
                            "uf_soins_ATU"                    => "custom tpl|inc_config_uf_soins",
                            "charge_UHCD"                     => "custom tpl|inc_config_charge",
                            "charge_ATU"                      => "custom tpl|inc_config_charge",
                            "uf_medicale_UHCD"                => "custom tpl|inc_config_uf_medicale",
                            "uf_medicale_ATU"                 => "custom tpl|inc_config_uf_medicale",
                            "motif_rpu_view"                  => "bool default|1",
                            "change_group"                    => "bool default|0",
                            "resultats_rpu_field_view"        => "bool default|0",
                            "required_orientation"            => "bool default|0",
                            "display_aut_eff_sortie_button"   => "bool default|1",
                            "initialiser_sortie_prevue"       => "enum list|sameday|h24|h48|h72 default|sameday localize",
                            "init_duree_prev_mutation_hospit" => "num min|0 default|0",
                            "provenance_UHCD"                 => "enum list||1|2|3|4|5|6|7|8|R default| localize",
                            "impose_transport_sortie"         => "bool default|0",
                            "box_id_mandatory"                => "bool default|0",
                            "prat_affectation"                => "bool default|1",
                            "criteres_passage_uhcd"           => "bool default|0",
                            "type_sejour"                     => "enum list|urg|urg_consult default|urg localize",
                            "french_triage"                   => "bool default|0",
                            'hospi_with_urgentiste'           => 'bool default|0',
                            'interdire_mutation_hospit'       => 'bool default|0',
                        ],
                        "Display"       => [
                            "check_cotation"         => "enum list|0|1|2 default|1 localize",
                            "check_gemsa"            => "enum list|0|1|2  default|1 localize",
                            "check_ccmu"             => "enum list|0|1|2  default|1 localize",
                            "check_dp"               => "enum list|0|1|2  default|1 localize",
                            "check_can_leave"        => "enum list|0|1  default|1 localize",
                            "see_ide_ref"            => "bool default|1",
                            "check_date_pec_inf"     => "bool default|0",
                            "check_date_pec_ioa"     => "bool default|0",
                            "demandes_radio_bio"     => "bool default|0",
                            "see_type_radio"         => "bool default|0",
                            "color_ccmu_1"           => "color default|0F0",
                            "color_ccmu_P"           => "color default|0F0",
                            "color_ccmu_2"           => "color default|9F0",
                            "color_ccmu_3"           => "color default|FF0",
                            "color_ccmu_4"           => "color default|FFCD00",
                            "color_ccmu_5"           => "color default|F60",
                            "color_ccmu_D"           => "color default|F00",
                            "display_cimu"           => "bool default|0",
                            "display_order"          => "enum list|ccmu|cimu|french_triage default|ccmu",
                            "color_cimu_5"           => "color default|80d3ec",
                            "color_cimu_4"           => "color default|058dc1",
                            "color_cimu_3"           => "color default|a9eea9",
                            "color_cimu_2"           => "color default|eee869",
                            "color_cimu_1"           => "color default|ee6969",
                            "color_french_triage_1"  => "color default|F40907",
                            "color_french_triage_2"  => "color default|F3C40E",
                            "color_french_triage_3A" => "color default|C307C4",
                            "color_french_triage_3B" => "color default|8DC850",
                            "color_french_triage_4"  => "color default|9CDDF5",
                            "color_french_triage_5"  => "color default|2F71B0",
                            "limit_reconvocations"   => "bool default|0",
                        ],
                        "send_RPU"      => [
                            "max_patient" => "num",
                            "totbox"      => "num min|0",
                            "totdechoc"   => "num min|0",
                            "totporte"    => "num min|0",
                        ],
                        "Print"         => [
                            "gemsa" => "bool default|0",
                        ],
                        "Placement"     => [
                            "placement_anonyme"             => "bool default|0",
                            "use_reservation_box"           => "bool default|0",
                            "display_background_color_ccmu" => "bool default|0",
                            "display_icon_sfmu"             => "bool default|1",
                            "display_reason_sfmu"           => "bool default|1",
                            "color_1"                       => "color",
                            "color_2"                       => "color",
                            "color_3"                       => "color",
                            "color_4"                       => "color",
                            "color_5"                       => "color",
                            "superposition_service"         => "bool default|1",
                        ],
                        "CConsultation" => [
                            'close_urg_with_inscription' => "bool default|1",
                        ],
                        "pec_transport" => [
                            "perso"      => "enum list|med|paramed|aucun default|aucun localize",
                            "perso_taxi" => "enum list|med|paramed|aucun default|aucun localize",
                            "ambu"       => "enum list|med|paramed|aucun default|aucun localize",
                            "ambu_vsl"   => "enum list|med|paramed|aucun default|aucun localize",
                            "vsab"       => "enum list|med|paramed|aucun default|aucun localize",
                            "smur"       => "enum list|med|paramed|aucun default|aucun localize",
                            "heli"       => "enum list|med|paramed|aucun default|aucun localize",
                            "fo"         => "enum list|med|paramed|aucun default|aucun localize",
                        ],
                    ],
                ],
            ]
        );
    }
}
