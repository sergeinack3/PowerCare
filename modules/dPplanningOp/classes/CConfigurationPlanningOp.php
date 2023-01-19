<?php

/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * Class CConfigurationPlanningOp
 */
class CConfigurationPlanningOp extends AbstractConfigurationRegister
{

    /**
     * @return void
     */
    public function register(): void
    {
        $sortie_prevue_values = [
            '1/4',
            '1/2',
            '01',
            '02',
            '03',
            '04',
            '24',
            '48',
            '72',
            '96',
            '120',
            '144',
            '168',
            '192',
            '216',
            '264',
            '288',
            '312',
            '336',
            '360',
            '384',
            '408',
            '432',
            '480',
            '504',
            '528',
            '552',
            '576',
            '600',
            '624',
            '648',
            '672',
            '696',
            '720',
            '744',
        ];
        $sortie_prevue_values = implode('|', $sortie_prevue_values);

        CConfiguration::register(
            [
                "CGroups" => [
                    "dPplanningOp" => [
                        "CSejour"               => [
                            "pass_to_confirm"                    => "bool default|0",
                            'entree_pre_op_ambu'                 => 'bool default|0',
                            'use_charge_price_indicator'         => 'enum list|no|opt|obl localize default|no',
                            "required_destination"               => "bool default|0",
                            "required_mode_entree"               => "bool default|0",
                            "required_mode_sortie"               => "bool default|0",
                            "required_uf_soins"                  => "enum list|no|opt|obl localize default|no",
                            "required_uf_med"                    => "enum list|no|opt|obl localize default|no",
                            "ssr_not_collides"                   => "bool default|0",
                            "facturable_distinct_not_collides"   => "bool default|0",
                            "use_phone"                          => "bool default|0",
                            "required_dest_when_transfert"       => 'bool default|0',
                            "required_dest_when_mutation"        => 'bool default|0',
                            "sortie_reelle_ambu"                 => "bool default|0",
                            "synchro_autorisation_sortie_prevue" => "bool default|0",
                            "show_tutelle"                       => "bool default|0",
                            "show_aide_organisee"                => "bool default|0",
                            "dhe_urgences_lite"                  => "bool default|0",
                            "mode_entree_dhe_urgences_lite"      => "custom tpl|inc_config_mode_entree",
                            'cancel_sortie_preparee'             => 'bool default|0',
                            "select_first_traitement"            => "bool default|0",
                            "tab_protocole_DHE_only_for_admin"   => "bool default|0",
                            "use_uf_sejour_to_affectation"       => "enum list|all|first localize default|all",
                            "new_dhe"                            => "bool default|0",
                            "required_atnc"                      => "bool default|0",
                            "show_nuit_convenance"               => "bool default|0",
                            "show_dmi_prevu"                     => "bool default|0",
                            'dhe_date_min'                       => 'date',
                            'dhe_date_max'                       => 'date',
                            "hospi_comp_jour"                    => "bool default|0",
                            "seance_preselect"                   => "bool default|0",
                            "update_sortie_prevue"               => "bool default|0",
                            "only_ufm_first_second"              => "bool default|0",
                            "multiple_affectation_pread"         => "bool default|1",
                            "hdj_seance"                         => "bool default|0",
                            "hospit_seance"                      => "bool default|0",
                            "sejour_type_duree_nocheck"          => "bool default|0",
                            "administration_with_timings_op"     => "bool default|0",
                            "med_trait_mandatory"                => "bool default|0",
                            "email_mandatory"                    => "bool default|0",
                            "tel_mandatory"                      => "bool default|0",
                            "ald_mandatory"                      => "bool default|0",
                            "check_tutelle"                      => "bool default|0",
                            "update_ufs_first_aff"               => "bool default|1",
                            "allow_fusion_sejour"                => "bool default|0",
                            "show_circuit_ambu"                  => "bool default|0",
                            "check_collisions"                   => "enum list|no|date|datetime default|date localize",
                            "use_prat_aff"                       => "bool default|0",
                            "fields_display"                     => [
                                "accident"                  => "bool default|0",
                                "assurances"                => "bool default|0",
                                "show_discipline_tarifaire" => "bool default|0",
                                "fiche_rques_sej"           => "bool default|0",
                                "fiche_conval"              => "bool default|0",
                                "show_c2s_ald"              => "bool default|0",
                                "show_days_duree"           => "bool default|0",
                                "show_isolement"            => "bool default|0",
                                "show_chambre_part"         => "bool default|0",
                                "show_facturable"           => "bool default|0",
                                "show_atnc"                 => "bool default|0",
                                "show_hospit_de_jour"       => "bool default|0",
                                "show_type_pec"             => "enum list|hidden|show|mandatory default|hidden localize"
                            ],
                            'default_hours'                      => [
                                'heure_entree_veille' => 'num min|0 max|23 default|17',
                                'heure_entree_jour'   => 'num min|0 max|23 default|10',
                                'min_entree_jour'     => 'num min|0 max|59 default|0',
                                'heure_sortie_ambu'   => 'num min|0 max|23 default|18',
                                'heure_sortie_autre'  => 'num min|0 max|23 default|8',
                            ],
                            'sortie_prevue'                      => [
                                'comp'    => "enum list|{$sortie_prevue_values} localize default|04",
                                'ambu'    => "enum list|{$sortie_prevue_values} localize default|04",
                                'exte'    => "enum list|{$sortie_prevue_values} localize default|04",
                                'seances' => "enum list|{$sortie_prevue_values} localize default|04",
                                'ssr'     => "enum list|{$sortie_prevue_values} localize default|24",
                                'psy'     => "enum list|{$sortie_prevue_values} localize default|24",
                                'urg'     => "enum list|{$sortie_prevue_values} localize default|24",
                                'consult' => "enum list|{$sortie_prevue_values} localize default|04",
                            ],
                        ],
                        "COperation"            => [
                            "multiple_label"                   => "bool default|0",
                            "multi_salle_op"                   => "bool default|0",
                            "adjust_debut_op"                  => "bool default|0",
                            "only_admin_can_change_time_op"    => "bool default|0",
                            "duree_bio_nettoyage_inf_or_eq_30" => "num min|0",
                            "duree_bio_nettoyage_sup_30"       => "num min|0",
                            "protocole_mandatory"              => "bool default|0",
                        ],
                        "CProtocole"            => [
                            "pct_ecart_tps_median"          => "num default|0",
                            "use_protocole_current_etab"    => "bool default|0",
                            "link_auto_label_protocole_dhe" => "bool default|0",
                        ],
                        "CFactureEtablissement" => [
                            "use_facture_etab" => "bool default|0",
                        ],
                    ],
                ],
            ]
        );
    }
}
