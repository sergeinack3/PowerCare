<?php

/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp;

use Exception;
use Ox\Core\CAppUI;
use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

class CConfigurationSalleOp extends AbstractConfigurationRegister
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function register()
    {
        CConfiguration::register(
            [
                "CGroups" => [
                    "dPsalleOp" => [
                        "General"             => [
                            "anesth_mode" => "bool default|0",
                        ],
                        "Timing_list"         => [
                            "max_add_minutes" => "num min|0 default|10",
                            "max_sub_minutes" => "num min|0 default|30",
                        ],
                        "COperation"          => [
                            "mode"                                     => "bool default|0",
                            "allow_change_room"                        => "bool default|0",
                            "use_sortie_reveil_reel"                   => "bool default|0",
                            "password_sortie"                          => "bool default|0",
                            "use_time_vpa"                             => "bool default|0",
                            "garrots_multiples"                        => "bool default|0",
                            "set_debutprepapreop_on_postepreop_choice" => "bool default|0",
                            "hide_timing_date"                         => "bool default|0",
                            'hide_visite_pre_anesth'                   => 'bool default|0',
                            'modif_actes'                              => 'custom tpl|inc_config_modif_actes default|oneday',
                            'modif_actes_worked_days'                  => 'bool default|0',
                            "no_entree_fermeture_salle_in_plage"       => "bool default|0",
                            'visibilite_depassement'                   => 'enum list|everyone|practitioners|functions|functions_and_practitioners|executant default|everyone localize',
                            "check_identity_pat"                       => "bool default|0",
                            "category_document_pre_anesth"             => "custom tpl|inc_config_category_summary",
                            "numero_panier_mandatory"                  => "bool default|0",
                        ],
                        "timings"             => [
                            "use_check_timing"         => "bool default|0",
                            "use_entry_exit_room"      => "bool default|1",
                            "use_exit_without_sspi"    => "bool default|0",
                            "use_garrot"               => "bool default|1",
                            "use_end_op"               => "bool default|1",
                            "use_entry_room"           => "bool default|0",
                            "use_delivery_surgeon"     => "bool default|0",
                            "use_suture"               => "bool default|0",
                            "use_cleaning_timings"     => "bool default|0",
                            "use_prep_cutanee"         => "bool default|0",
                            "use_preparation_op"       => "bool default|0",
                            "use_tto"                  => "bool default|0",
                            "use_debut_installation"   => "bool default|0",
                            "use_fin_installation"     => "bool default|0",
                            "see_pec_anesth"           => "bool default|0",
                            "place_pec_anesth"         =>
                                "enum list|end_preparation|under_entree_bloc default|end_preparation localize",
                            "place_remise_chir"        =>
                                "enum list|below_entree_salle|under_entree_salle default|below_entree_salle localize",
                            "timings_induction"        => "bool default|0",
                            "use_alr_ag"               => "bool default|0",
                            "use_incision"             => "bool default|0",
                            "see_entree_reveil_timing" => "bool default|0",
                            "use_sortie_sejour_ext"    => "bool default|0",
                            'use_validation_timings'   => 'bool default|0',
                            "see_remise_anesth"        => "bool default|0",
                            "see_patient_stable"       => "bool default|0",
                            "see_fin_pec_anesth"       => "bool default|0",
                        ],
                        "hors_plage"          => [
                            "type_anesth"         => "bool default|0",
                            "heure_entree_sejour" => "bool default|0",
                        ],
                        "CDailyCheckList"     => [
                            "active_salle_reveil"       => "bool default|0",
                            "choose_moment_edit"        => "bool default|0",
                            "choose_open_salle"         => "bool default|0",
                            "multi_check_sspi"          => "bool default|0",
                            "multi_check_preop"         => "bool default|0",
                            "presence_for_sortie_salle" => "bool default|0",
                        ],
                        "Default_good_answer" => [
                            "default_good_answer_COperation"                => "bool default|0",
                            "default_good_answer_CSalle"                    => "bool default|0",
                            "default_good_answer_CBlocOperatoire"           => "bool default|0",
                            "default_good_answer_CPoseDispositifVasculaire" => "bool default|0",
                            "default_good_answer_CSSPI"                     => "bool default|0",
                        ],
                        "SSPI_cell"           => [
                            "see_type_anesth"  => "bool default|0",
                            "see_ctes"         => "bool default|0",
                            "see_localisation" => "bool default|1",
                        ],
                        "SSPI"                => [
                            "see_sspi_bloc" => "bool default|1",
                        ],
                        "supervision_graph"   => [
                            "lock_supervision_graph" => "enum list|0|2|4 default|0 localize",
                        ],
                        "CActeCCAM"           => [
                            "check_incompatibility"       =>
                                "enum list|block|blockOperationAlertOthers|alert|allow default|allow localize",
                            "allow_send_acts_room"        => "bool default|0",
                            "allow_send_reason_exceeding" => "bool default|1",
                            "del_acts_not_rated"          => "bool default|0",
                        ],
                    ],
                ],
            ]
        );
    }
}
