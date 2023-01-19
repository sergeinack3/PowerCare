<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * Class CConfigurationCabinet
 */
class CConfigurationCabinet extends AbstractConfigurationRegister
{

    /**
     * @return mixed
     */
    public function register()
    {
        $listHours = "";
        foreach (range(0, 23) as $_hour) {
            $hour = str_pad($_hour, 2, "0", STR_PAD_LEFT);
            if ($listHours) {
                $listHours .= "|";
            }
            $listHours .= $hour;
        }

        $config_etab_base = [
            "CGroups" => [
                "dPcabinet" => [
                    "CConsultation"    => [
                        "allow_city_consultation"           => "bool default|0",
                        "complete_atcd_mode_grille"         => "bool default|0",
                        "use_old_anesth_conclu"             => "bool default|0",
                        "csa_duplicate_by_cabinet"          => "bool default|0",
                        "cancel_rdv"                        => "bool default|0",
                        "close_cotation_rights"             => "enum list|everyone|owner_only default|everyone localize",
                        "verification_access"               => "bool default|1 localize",
                        "duree_sejour_creation_rdv"         => "num min|1",
                        "use_last_consult"                  => "bool default|0",
                        "show_examen"                       => "bool default|1",
                        "show_histoire_maladie"             => "bool default|0",
                        "show_projet_soins"                 => "bool default|0",
                        "show_conclusion"                   => "bool default|0",
                        "show_IPP_print_consult"            => "bool default|0",
                        "attach_consult_sejour"             => "bool default|0",
                        "create_consult_sejour"             => "bool default|0",
                        "minutes_before_consult_sejour"     => "num min|0 default|1",
                        "hours_after_changing_prat"         => "num min|0 default|0",
                        "fix_doc_edit"                      => "bool default|0",
                        "search_sejour_all_groups"          => "bool default|0",
                        "same_year_charge_id"               => "bool default|0",
                        "consult_readonly"                  => "bool default|0",
                        "surbooking_readonly"               => "bool default|1",
                        "tag"                               => "str",
                        "default_taux_tva"                  => "str default|0|19.6",
                        "auto_refresh_frequency"            => "enum list|90|180|300|600 default|90",
                        "default_message_immediate_consult" => "str default|CConsultation-action-Immediate localize",
                    ],
                    "PriseRDV"         => [
                        "keepchir"                             => "bool default|1",
                        "display_nb_consult"                   => "enum list|none|cab|etab default|cab localize",
                        "display_practitioner_name_future_rdv" => "bool default|1",
                    ],
                    "CPlageconsult"    => [
                        "hours_start"      => "enum list|$listHours default|8",
                        "hours_stop"       => "enum list|$listHours default|20",
                        "minutes_interval" => "enum list|05|10|15|20|30 default|15",
                        "hour_limit_matin" => "enum list|$listHours default|12",
                    ],
                    "CDevisCodage"     => [
                        'codage_interv_anesth' => "bool default|0",
                    ],
                    "CPrescription"    => [
                        "view_prescription"         => "bool default|0",
                        "view_prescription_externe" => "bool default|0",
                    ],
                    'Planning'         => [
                        "show_print_order_mode"           => "bool default|0",
                        "auto_refresh_planning_frequency" => "enum list|0|90|180|300|600 default|0 localize",
                    ],
                    'CConsultAnesth'   => [
                        'active'                    => 'bool default|1',
                        "text_atcd_nvp"             => "str default|NVPO",
                        "see_strategie_prevention"  => "bool default|0",
                        "stock_pdf_anesth_on_close" => "bool default|0",
                        "risque_intubation_auto"    => "bool default|1",
                        "feuille_anesthesie"        => "enum list|print_fiche|print_fiche1 default|print_fiche localize",
                        "format_auto_motif"         => "str",
                        "format_auto_rques"         => "str",
                        "view_premedication"        => "bool default|0",
                        "show_facteurs_risque"      => "bool default|0",
                    ],
                    "Tarifs"           => [
                        "show_tarifs_etab" => "bool default|0",
                    ],
                    "Comptabilite"     => [
                        "show_compta_tiers" => "bool default|1",
                    ],
                    "Summary"          => [
                        "model_id"    => "custom tpl|inc_config_model_summary",
                        "category_id" => "custom tpl|inc_config_category_summary",
                    ],
                    "CAccidentTravail" => [
                        "show_new_view_at" => "bool default|0",
                    ],
                ],
            ],
            "CFunctions CGroups.group_id" => [
                "dPcabinet" => [
                    "ConsultationImmediate" => [
                        "slots" => "enum list|1|5|10|15|20|30|60 default|1 localize",
                    ],
                ],
            ],
        ];

        CConfiguration::register($config_etab_base);
    }
}
