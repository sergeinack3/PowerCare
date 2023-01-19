<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\CAppUI;
use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * Class CConfigurationPatients
 */
class CConfigurationPatients extends AbstractConfigurationRegister
{

    /**
     * @return mixed
     */
    public function register()
    {
        $selection = [
            "poids"                  => "str components|form|graph|color|mode|min|max|norm_min|norm_max default|1|0|0066FF|float|2|2|0|0",
            "taille"                 => "str components|form|graph|color|mode|min|max|norm_min|norm_max default|2|0|0066FF|float|5|5|0|0",
            "pouls"                  => "str components|form|graph|color|mode|min|max|norm_min|norm_max default|3|1|FF0000|fixed|70|120|90|0",
            "temperature"            => "str components|form|graph|color|mode|min|max|norm_min|norm_max default|4|1|0066FF|fixed|36|40|37.5|0",
            "ta"                     => "str components|form|graph|color|mode|min|max|norm_min|norm_max default|5|1|000000|fixed|2|16|8|12",
            "EVA"                    => "str components|form|graph|color|mode|min|max|norm_min|norm_max default|6|1|FF00FF|fixed|0|10|0|0",
            "frequence_respiratoire" => "str components|form|graph|color|mode|min|max|norm_min|norm_max default|7|1|009900|fixed|0|60|0|0",
        ];
        $alerts    = [];
        $comment   = [];

        foreach (CConstantesMedicales::$list_constantes as $_constante => $_params) {
            if (!isset($_params["cumul_for"])) {
                if (!in_array(
                    $_constante,
                    ['poids', 'taille', 'pouls', 'temperature', 'ta', 'EVA', 'frequence_respiratoire']
                )) {
                    $mode     = 'fixed';
                    $min      = $_params['min'];
                    $max      = $_params['max'];
                    $norm_min = 0;
                    $norm_max = 0;
                    if (strpos($_params['min'], '@') !== false || strpos($_params['max'], '@') !== false) {
                        $mode = 'float';
                        $min  = str_replace('@-', '', $_params['min']);
                        $max  = str_replace('@+', '', $_params['max']);
                    }
                    if (array_key_exists('norm_min', $_params)) {
                        $norm_min = $_params['norm_min'];
                    }
                    if (array_key_exists('norm_max', $_params)) {
                        $norm_max = $_params['norm_max'];
                    }
                    $selection[$_constante] = "str components|form|graph|color|mode|min|max|norm_min|norm_max default|0|0|0066FF|$mode|$min|$max|$norm_min|$norm_max";
                }
                $alerts[$_constante]  = "str components|lower_threshold|upper_threshold|lower_text|upper_text default||||";
                $comment[$_constante] = "bool default|0";
            }
        }

        CConfiguration::register(
            [
                'CGroups' => [
                    'dPpatients' => [
                        'CPatient'             => [
                            'limit_char_search'              => 'enum list|0|3|4|5|6|8|10 localize default|0',
                            'extended_print'                 => 'bool default|0',
                            'check_code_insee'               => 'bool default|1',
                            'nom_jeune_fille_always_present' => 'bool default|0',
                            'allow_anonymous_patient'        => 'bool default|0',
                            'anonymous_naissance'            => 'str default|1970-01-01',
                            'anonymous_sexe'                 => 'enum list|m|f default|m',
                            'manage_identity_vide'           => 'bool default|1',
                            'auto_selected_patient'          => 'bool default|0',
                            'default_value_allow_sms'        => 'bool default|0',
                            'default_code_regime'            => 'numchar length|2',
                            'search_paging'                  => 'bool default|0',
                            'overweight'                     => 'num default|120',
                            'cp_patient_mandatory'           => 'bool default|0',
                            'tel_patient_mandatory'          => 'bool default|0',
                            'addr_patient_mandatory'         => 'bool default|0',
                            'alert_email_a_jour'             => 'bool default|0',
                            'alert_telephone_a_jour'         => 'bool default|0',
                            'alert_adresse_a_jour'           => 'bool default|0',
                            'adult_age'                      => 'num default|15',
                            'tutelle_mandatory'              => 'bool default|0',
                            "show_rules_alert"               => "bool default|1",
                            'custom_matching'                => 'custom tpl|inc_config_matching_patient',
                            'allow_email_not_defined'        => 'bool default|0',
                        ],
                        'sharing'              => [
                            "multi_group"          => "enum list|full|limited|hidden localize default|limited",
                            "patient_data_sharing" => "bool default|0",
                        ],
                        'identitovigilance'    => [
                            'merge_only_admin' => "bool default|0",
                        ],
                        'CTraitement'          => [
                            'enabled'                   => "bool default|0",
                            'perso_gestion_dossier_med' => "bool default|0",
                        ],
                        'CAntecedent'          => [
                            "create_antecedent_only_prat"      => "bool default|0",
                            "create_treatment_only_prat"       => "bool default|0",
                            "show_atcd_by_tooltip"             => "bool default|0",
                            "display_antecedents_non_presents" => "bool default|1",
                        ],
                        'CConstantesMedicales' => [
                            'unite_ta'          => 'enum list|cmHg|mmHg default|' . CAppUI::conf(
                                    'dPpatients CConstantesMedicales unite_ta'
                                ),
                            'unite_glycemie'    => 'enum list|g/l|mmol/l|mg/dl|µmol/l default|' . CAppUI::conf(
                                    'dPpatients CConstantesMedicales unite_glycemie'
                                ),
                            'unite_cetonemie'   => 'enum list|g/l|mmol/l default|' . CAppUI::conf(
                                    'dPpatients CConstantesMedicales unite_cetonemie'
                                ),
                            'unite_hemoglobine' => 'enum list|g/dl|g/l default|g/dl',
                            'unite_ldlc'        => 'enum list|g/l|mmol/l default|g/l',
                            'unite_creatinine'  => 'enum list|mg/l|µmol/l default|mg/l',
                            'imc_threshold'     => 'num default|60',
                            "use_redon"         => "bool default|0",
                        ],
                        'CMedecin'             => [
                            'edit_for_admin' => 'bool default|0',
                        ],
                    ],
                ],

                'CService CGroups.group_id' => [
                    'dPpatients' => [
                        'CConstantesMedicales' => [
                            'show_cat_tabs'                       => 'bool default|0',
                            'constants_modif_timeout'             => 'num min|0 max|48 default|12',
                            'stacked_graphs'                      => 'bool default|0',
                            'graphs_display_mode'                 => 'custom tpl|inc_config_graphs_display_mode components|mode|time default|classic|8',
                            'diuere_24_reset_hour'                => 'num min|0 max|23 default|8',
                            'redon_cumul_reset_hour'              => 'num min|0 max|23 default|8',
                            'redon_accordeon_cumul_reset_hour'    => 'num min|0 max|23 default|8',
                            'sng_cumul_reset_hour'                => 'num min|0 max|23 default|8',
                            'lame_cumul_reset_hour'               => 'num min|0 max|23 default|8',
                            'drain_cumul_reset_hour'              => 'num min|0 max|23 default|8',
                            'drain_thoracique_cumul_reset_hour'   => 'num min|0 max|23 default|8',
                            'drain_pleural_cumul_reset_hour'      => 'num min|0 max|23 default|8',
                            'drain_mediastinal_cumul_reset_hour'  => 'num min|0 max|23 default|8',
                            'drain_dve_cumul_reset_hour'          => 'num min|0 max|23 default|8',
                            'drain_kher_cumul_reset_hour'         => 'num min|0 max|23 default|8',
                            'drain_crins_cumul_reset_hour'        => 'num min|0 max|23 default|8',
                            'drain_sinus_cumul_reset_hour'        => 'num min|0 max|23 default|8',
                            'drain_orifice_cumul_reset_hour'      => 'num min|0 max|23 default|8',
                            'drain_ileostomie_cumul_reset_hour'   => 'num min|0 max|23 default|8',
                            'drain_colostomie_cumul_reset_hour'   => 'num min|0 max|23 default|8',
                            'drain_gastrostomie_cumul_reset_hour' => 'num min|0 max|23 default|8',
                            'drain_jejunostomie_cumul_reset_hour' => 'num min|0 max|23 default|8',
                            'sonde_ureterale_cumul_reset_hour'    => 'num min|0 max|23 default|8',
                            'sonde_nephro_cumul_reset_hour'       => 'num min|0 max|23 default|8',
                            'sonde_vesicale_cumul_reset_hour'     => 'num min|0 max|23 default|8',
                            'sonde_rectale_cumul_reset_hour'      => 'num min|0 max|23 default|8',
                            'urine_effective_24_reset_hour'       => 'num min|0 max|23 default|8',
                            'bilan_hydrique_reset_hour'           => 'num min|0 max|23 default|8',
                            'bilan_hydrique_granularite'          => 'enum list|2|4|8|12|24 default|8',
                            'jackson_cumul_reset_hour'            => 'num min|0 max|23 default|8',
                            'scurasil_cumul_reset_hour'           => 'num min|0 max|23 default|8',
                            'psl_cumul_reset_hour'                => 'num min|0 max|23 default|8',
                            'activate_choice_blood_glucose_units' => 'bool default|0',
                        ],
                    ],
                ],

                'CFunctions CGroups.group_id' => [
                    'dPpatients' => [
                        'CConstantesMedicales' => [
                            'show_cat_tabs'  => 'bool default|0',
                            'stacked_graphs' => 'bool default|0',
                        ],
                    ],
                ],

                'CBlocOperatoire CGroups.group_id' => [
                    'dPpatients' => [
                        'CConstantesMedicales' => [
                            'show_cat_tabs'  => 'bool default|0',
                            'stacked_graphs' => 'bool default|0',
                        ],
                    ],
                ],

                "constantes / CService CGroups.group_id" => [
                    "dPpatients" => [
                        "CConstantesMedicales" => [
                            "selection" => $selection,
                            'alerts'    => $alerts,
                            'comment'   => $comment,
                        ],
                    ],
                ],

                "constantes / CFunctions CGroups.group_id" => [
                    "dPpatients" => [
                        "CConstantesMedicales" => [
                            "selection_cabinet" => $selection,
                            'alerts_cabinet'    => $alerts,
                            'comment_cabinet'   => $comment,
                        ],
                    ],
                ],

                "constantes / CBlocOperatoire CGroups.group_id" => [
                    "dPpatients" => [
                        "CConstantesMedicales" => [
                            "selection_bloc" => $selection,
                            'alerts_bloc'    => $alerts,
                            'comment_bloc'   => $comment,
                        ],
                    ],
                ],
            ]
        );
    }
}
