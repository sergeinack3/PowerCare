<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins\Controllers\Legacy;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\Mpm\CPrescriptionLineMix;
use Ox\Mediboard\OxLaboClient\OxLaboClient;
use Ox\Mediboard\OxLaboClient\OxLaboClientHandler;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLineComment;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Mediboard\Soins\CSejourTask;
use Ox\Mediboard\Soins\Services\AffectationService;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceHTTP;

/**
 * Class SejourController
 * @package Ox\Mediboard\Soins\Controllers\Legacy
 */
class SejourController extends CLegacyController
{
    /**
     * @throws \Exception
     */
    public function vwSejours(): void
    {
        $service_id       = CValue::get("service_id");
        $praticien_id     = CValue::get("praticien_id");
        $function_id      = CValue::get("function_id");
        $sejour_id        = CValue::get("sejour_id");
        $discipline_id    = CValue::get("discipline_id");
        $show_affectation = CValue::get("show_affectation", false);
        $only_non_checked = CValue::get("only_non_checked", 0);
        $print            = CValue::get("print", false);
        $_type_admission  = CValue::getOrSession("_type_admission", "");
        $select_view      = CValue::get("select_view", false);
        $refresh          = CValue::get('refresh', false);
        $ecap             = CValue::get('ecap', false);
        $date             = CValue::get('date', CMbDT::date());
        $viewMode         = CValue::get('viewMode');
        $lite_view        = CValue::get("lite_view");
        $my_patient       = CValue::getOrSession("my_patient");
        $services_ids     = CValue::getOrSession("services_ids", null);
        $board            = CValue::get("board");
        $validation_pharm = CView::get("show_validation_pharmacie", "bool default|0");

        $viewMode ? $view = 'day' : $view = 'instant';
        $mode = CValue::get('mode', $view);

        if (CAppUI::pref("use_current_day")) {
            $date = CMbDT::date();
        }

        // Mode Dossier de soins, chargement de la liste des service, praticiens, functions
        $services            = [];
        $functions           = [];
        $praticiens          = [];
        $dossiers            = [];
        $group               = CGroups::loadCurrent();
        $group_id            = $group->_id;
        $affectation_service = new AffectationService();

        $select_services_ids = CAppUI::gconf("soins Sejour select_services_ids") && !$ecap;

        if ($select_services_ids) {
            $services_ids = CService::getServicesIdsPref($services_ids);
            if ($services_ids) {
                $service_id = null;
            }
        } else {
            $services_ids = [];
        }

        if ($select_view || (!$service_id && (!$praticien_id || $praticien_id == 'none') && !$function_id && !$sejour_id && !$discipline_id)) {
            // Redirection pour gérer le cas ou le volet par defaut est l'autre affichage des sejours
            if (CAppUI::pref("vue_sejours") == "standard") {
                CAppUI::redirect("m=soins&tab=viewIndexSejour");
            }

            // Récupération d'un éventuel service_id en session
            $service_id         = CValue::getOrSession("service_id");
            $default_service_id = null;
            $default_services   = json_decode(CAppUI::pref("default_services_id"));
            if (isset($default_services->{"g$group_id"})) {
                $default_service_id = explode("|", $default_services->{"g$group_id"});
                $default_service_id = reset($default_service_id);
            }

            // Récupération d'un éventuel praticien_id ou function_id ou discipline_id en session
            if (!$service_id) {
                $praticien_id  = CValue::getOrSession("praticien_id");
                $function_id   = CValue::getOrSession("function_id");
                $discipline_id = CValue::getOrSession("discipline_id");
            }

            if (!$service_id && $default_service_id && (!$praticien_id || $praticien_id == 'none') && !$function_id && !$discipline_id && !$select_services_ids) {
                $service_id = $default_service_id;
            }

            $select_view = true;
        }

        // Si on est bien en sélection multiple de services, alors ne pas prendre en compte un éventuel service_id récupéré en session
        if ($select_services_ids && count($services_ids)) {
            $service_id = null;
        }

        CAppUI::requireModuleFile("dPhospi", "inc_vw_affectations");

        $hotellerie_active   = CModule::getActive("hotellerie");
        $prescription_active = CModule::getActive("dPprescription");

        // Chargement de l'utilisateur courant
        $userCourant = CMediusers::get();
        // Préselection du contexte
        if (!$praticien_id && !$function_id && !$discipline_id) {
            switch (CAppUI::pref("preselect_me_care_folder")) {
                case "1":
                    if ($userCourant->isPraticien()) {
                        $praticien_id = $userCourant->user_id;
                    }
                    break;
                case "2":
                    $function_id = $userCourant->function_id;
                    break;
                case "3":
                    if ($userCourant->isPraticien()) {
                        $discipline_id = $userCourant->discipline_id;
                    }
                    break;
                default:
            }
        }

        if ($praticien_id == 'none') {
            $praticien_id = '';
        }

        $where              = [];
        $where["externe"]   = "= '0'";
        $where["cancelled"] = "= '0'";
        $service            = new CService();
        $services           = $service->loadListWithPerms(PERM_READ, $where);

        $praticien  = new CMediusers();
        $praticiens = $praticien->loadListFromType(["Chirurgien", "Anesthésiste", "Médecin", "Dentiste", "Sage Femme"]);

        $function  = new CFunctions();
        $functions = $function->loadSpecialites();

        $date_max = CMbDT::date("+ 1 DAY", $date);
        $service  = new CService();
        $user_id  = CAppUI::$user->_id;

        if ($service_id) {
            CValue::setSession("service_id", $service_id);
        }
        if ($praticien_id) {
            CValue::setSession("praticien_id", $praticien_id);
        }
        if ($function_id) {
            CValue::setSession("function_id", $function_id);
        }
        if ($discipline_id) {
            CValue::setSession("discipline_id", $discipline_id);
        }

        CView::checkin();

        $praticien = new CMediusers();
        $praticien->load($praticien_id);
        $whereChir = " = ''";
        if ($praticien->_id && $praticien->isProfessionnelDeSante()) {
            $whereChir = $praticien->getUserSQLClause();
        }

        if ($sejour_id) {
            $sejour = new CSejour();
            $sejour->load($sejour_id);
            $sejours[$sejour_id] = $sejour;

            CAccessMedicalData::logAccess($sejour);
        }

        $use_prat_aff = CAppUI::gconf("dPplanningOp CSejour use_prat_aff");

        if (!isset($sejours)) {
            $see_np = $service_id == "NP" || in_array("NP", $services_ids);

            // Voir les non placés aussi si on a sélectionné :
            // - un praticien / cabinet / discipline
            // et sans aucun service sélectionné ou NP sélectionné
            $see_np = $see_np || (($praticien_id || $function_id || $discipline_id)
                    && ($service_id == "NP" || (!count($services_ids) || in_array("NP", $services_ids))));

            if ($see_np) {
                $sejour = new CSejour();

                $ljoin                = [];
                $ljoin["affectation"] = "affectation.sejour_id = sejour.sejour_id";

                $where                               = [];
                $where["sejour.entree"]              = "<= '$date_max'";
                $where["sejour.sortie"]              = ">= '$date'";
                $where["affectation.affectation_id"] = " IS NULL";
                $where["sejour.group_id"]            = " = '$group_id'";
                if ($praticien_id) {
                    $where["sejour.praticien_id"] = $whereChir;
                } else {
                    $where["sejour.praticien_id"] = CSQLDataSource::prepareIn(array_keys($praticiens));
                }

                $where["sejour.annule"] = " = '0'";

                if ($_type_admission) {
                    if ($_type_admission == 'ambucomp') {
                        $where["sejour.type"] = "IN ('ambu', 'comp')";
                    } elseif ($_type_admission == "ambucompssr") {
                        $where["sejour.type"] = "IN ('ambu', 'comp', 'ssr')";
                    } else {
                        $where["sejour.type"] = "= '$_type_admission'";
                    }
                }

                if ($function_id) {
                    $ljoin["users_mediboard"]    = "sejour.praticien_id = users_mediboard.user_id";
                    $ljoin["secondary_function"] = "sejour.praticien_id = secondary_function.user_id";
                    $where[]                     = "$function_id IN (users_mediboard.function_id, secondary_function.function_id)";
                } elseif ($discipline_id) {
                    $ljoin["users_mediboard"] = "sejour.praticien_id = users_mediboard.user_id";
                    $where[]                  = "users_mediboard.discipline_id = '$discipline_id'";
                }

                $sejours = $sejour->loadList($where, null, null, null, $ljoin);
                if (count($services_ids)) {
                    unset($services_ids[array_search("NP", $services_ids)]);
                }
            }

            if ($service_id != "NP" || count($services_ids)) {
                // Chargement du service
                $service->load($service_id);

                $key_prat = $use_prat_aff ? "affectation.praticien_id" : "sejour.praticien_id";

                // Chargement des sejours pour le service selectionné
                $ljoin            = [];
                $ljoin["lit"]     = "affectation.lit_id = lit.lit_id";
                $ljoin["chambre"] = "lit.chambre_id = chambre.chambre_id";
                $ljoin["sejour"]  = "affectation.sejour_id = sejour.sejour_id";

                $where                          = [];
                $where["affectation.sejour_id"] = "!= 0";
                $where["sejour.group_id"]       = "= '$group_id'";

                if ($praticien_id && !$service_id && !count($services_ids) && $praticien->isAnesth()) {
                    $ljoin["operations"] = "operations.sejour_id = sejour.sejour_id";
                    $ljoin["plagesop"]   = "operations.plageop_id = plagesop.plageop_id";
                    $where[]             = "operations.anesth_id $whereChir OR (operations.anesth_id IS NULL AND plagesop.anesth_id $whereChir)
                OR sejour.praticien_id $whereChir";
                } else {
                    if ($praticien_id) {
                        $where[$key_prat] = $whereChir;
                    } else {
                        $where[$key_prat] = CSQLDataSource::prepareIn(array_keys($praticiens));
                    }
                }
                $where["sejour.annule"]      = " = '0'";
                $where["affectation.entree"] = "<= '$date_max'";
                $where["affectation.sortie"] = ">= '$date'";

                if ($mode == 'instant') {
                    $where[] = "affectation.effectue = '0' OR sejour.sortie_reelle >= '" . CMbDT::dateTime() . "'";
                    $where[] = "'" . CMbDT::dateTime() . "' BETWEEN affectation.entree AND affectation.sortie";
                }

                if ($_type_admission) {
                    if ($_type_admission == 'ambucomp') {
                        $where["sejour.type"] = "IN ('ambu', 'comp')";
                    } elseif ($_type_admission == "ambucompssr") {
                        $where["sejour.type"] = "IN ('ambu', 'comp', 'ssr')";
                    } else {
                        $where["sejour.type"] = "= '$_type_admission'";
                    }
                }

                if ($service_id) {
                    $where["affectation.service_id"] = " = '$service_id'";
                } elseif (count($services_ids)) {
                    $where["affectation.service_id"] = CSQLDataSource::prepareIn(array_values($services_ids));
                } elseif (!count($services_ids) && !$service_id) {
                    // Prendre en compte les services permis de l'utilisateur sélectionné (ex: secrétaire)
                    $where["affectation.service_id"] = CSQLDataSource::prepareIn(array_merge(array_keys($services), array('NP')), $service_id);
                }
                if ((!$service_id || count($services_ids)) && $function_id) {
                    $ljoin["users_mediboard"]    = "$key_prat = users_mediboard.user_id";
                    $ljoin["secondary_function"] = "$key_prat = secondary_function.user_id";
                    $where[]                     = "$function_id IN (users_mediboard.function_id, secondary_function.function_id)";
                }

                if ((!$service_id || count($services_ids)) && $discipline_id) {
                    $ljoin["users_mediboard"] = "sejour.praticien_id = users_mediboard.user_id";
                    $where[]                  = "users_mediboard.discipline_id = '$discipline_id'";
                }

                if ($praticien_id && $only_non_checked) {
                    $where_line = [];

                    $user_id                     = CMediusers::get()->_id;
                    $where_line["sejour.entree"] = "<= '$date_max'";
                    $where_line["sejour.sortie"] = ">= '$date'";
                    $where_line["sejour.annule"] = " = '0'";

                    $where_line["prescription.type"]                            = " = 'sejour'";
                    $ljoin_line                                                 = [];
                    $ljoin_line["prescription"]                                 = "prescription.prescription_id = prescription_line_medicament.prescription_id";
                    $ljoin_line["sejour"]                                       = "prescription.object_id = sejour.sejour_id";
                    $where_line["prescription_line_medicament.praticien_id"]    = $whereChir;
                    $where_line["prescription_line_medicament.substituted"]     = " = '0'";
                    $where_line["prescription_line_medicament.variante_for_id"] = "IS NULL";
                    $where_line["prescription_line_medicament.variante_active"] = " = '1'";

                    // Lignes de médicament
                    if (CPrescription::isMPMActive()) {
                        $line  = new CPrescriptionLineMedicament();
                        $lines = $line->loadList($where_line, null, null, null, $ljoin_line);
                        /* @var CPrescriptionLineMedicament[] $lines */
                        foreach ($lines as $_line) {
                            $_line->loadRefPrescription();
                            $_sejour = $_line->_ref_prescription->_ref_object;
                            if (!isset($sejours[$_sejour->_id])) {
                                $sejours[$_sejour->_id] = $_sejour;
                            }
                        }

                        unset($where_line["prescription_line_medicament.substituted"]);
                        unset($where_line["prescription_line_medicament.variante_for_id"]);
                        unset($where_line["prescription_line_medicament.variante_active"]);
                    }

                    // Lignes de commentaire
                    $ljoin_line                 = [];
                    $line                       = new CPrescriptionLineComment();
                    $ljoin_line["prescription"] = "prescription.prescription_id = prescription_line_comment.prescription_id";
                    $ljoin_line["sejour"]       = "prescription.object_id = sejour.sejour_id";
                    unset($where_line["prescription_line_medicament.praticien_id"]);
                    $where_line["prescription_line_comment.praticien_id"] = $whereChir;


                    $lines = $line->loadList($where_line, null, null, null, $ljoin_line);

                    foreach ($lines as $_line) {
                        $_line->loadRefPrescription();
                        $_sejour = $_line->_ref_prescription->_ref_object;
                        if (!isset($sejours[$_sejour->_id])) {
                            $sejours[$_sejour->_id] = $_sejour;
                        }
                    }

                    // Lignes d'éléments
                    $ljoin_line = [];
                    $line       = new CPrescriptionLineElement();
                    unset($where_line["prescription_line_comment.praticien_id"]);
                    $where_line["prescription_line_element.praticien_id"] = $whereChir;
                    $ljoin_line["prescription"]                           = "prescription.prescription_id = prescription_line_element.prescription_id";
                    $ljoin_line["sejour"]                                 = "prescription.object_id = sejour.sejour_id";

                    $lines = $line->loadList($where_line, null, null, null, $ljoin_line);

                    foreach ($lines as $_line) {
                        $_line->loadRefPrescription();
                        $_sejour = $_line->_ref_prescription->_ref_object;
                        if (!isset($sejours[$_sejour->_id])) {
                            $sejours[$_sejour->_id] = $_sejour;
                        }
                    }

                    // Lignes mixes
                    if (CPrescription::isMPMActive()) {
                        $where_line["prescription_line_mix.variante_for_id"] = "IS NULL";
                        $where_line["prescription_line_mix.variante_active"] = " = '1'";
                        $where_line["prescription_line_mix.substituted"]     = "= '0'";
                        $ljoin_line                                          = [];
                        $line_mix                                            = new CPrescriptionLineMix();
                        unset($where_line["prescription_line_element.praticien_id"]);
                        $where["prescription_line_mix.praticien_id"] = $whereChir;
                        unset($where_line["signee"]);
                        $ljoin_line["prescription"] = "prescription.prescription_id = prescription_line_mix.prescription_id";
                        $ljoin_line["sejour"]       = "prescription.object_id = sejour.sejour_id";

                        $lines = $line_mix->loadList($where_line, null, null, null, $ljoin_line);

                        foreach ($lines as $_line) {
                            $_line->loadRefPrescription();
                            $_sejour = $_line->_ref_prescription->_ref_object;
                            if (!isset($sejours[$_sejour->_id])) {
                                $sejours[$_sejour->_id] = $_sejour;
                            }
                        }
                    }
                } else {
                    $sejours = isset($sejours) ? $sejours : [];
                    if ($service_id || count($services_ids) || $praticien_id || $function_id || $discipline_id) {
                        $affectations = $affectation_service->loadAffectations($where, $ljoin, $services_ids, $service_id, $date, $mode);
                        $sejours = $affectation_service->updateSejoursFromAffectations($affectations, $sejours);
                        $affectation_service->prepareAffectations($affectations, $hotellerie_active, $date, $mode);
                    }
                }
            }
        }

        /* @var CPatient[] $patients */
        $patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
        CPatient::massLoadIPP($patients);
        CStoredObject::massLoadBackRefs($patients, "dossier_medical");
        CStoredObject::massLoadBackRefs($patients, "bmr_bhre");

        CStoredObject::massLoadFwdRef($sejours, "praticien_id");
        CStoredObject::massCountBackRefs($sejours, "tasks", ["realise" => "= '0'"], [], "taches_non_realisees");
        CStoredObject::massLoadBackRefs($sejours, "dossier_medical");
        CStoredObject::massLoadBackRefs($sejours, "operations", "date ASC", ["annulee" => "= '0'"]);
        CSejour::massLoadSurrAffectation($sejours);
        CSejour::massLoadBackRefs($sejours, "user_sejour");
        CSejour::massLoadNDA($sejours);

        $type_view_demande_particuliere = CAppUI::pref("type_view_demande_particuliere");
        $degre                          = $type_view_demande_particuliere == "last_macro" ? null : "low";
        if (in_array($type_view_demande_particuliere, ["trans_hight", "macro_hight"])) {
            $degre = "high";
        }
        $cible_importante = in_array($type_view_demande_particuliere, ["last_macro", "macro_low", "macro_hight"]) ? true : false;
        $important        = $cible_importante ? false : true;

        $alert_handler = HandlerManager::isObjectHandlerActive('CPrescriptionAlerteHandler');

        $count_my_patient = 0;

        $where_affectation = [];

        if ($validation_pharm) {
            $count_validation_pharma = [];
        }

        $pharmacie_active    = CModule::getActive("pharmacie");
        $see_risque_pop      = $prescription_active && CAppUI::gconf("mpm Analyse_cat see_risque_pop");
        $see_lines_no_signed = $prescription_active && CAppUI::gconf("mpm Analyse_pharma see_lines_no_signed");
        foreach ($sejours as $sejour) {
            $count_my_patient += count($sejour->loadRefsUserSejour($userCourant, $date, $mode));
            $sejour->loadRefPatient()->updateBMRBHReStatus($sejour);
            $sejour->loadRefPraticien();
            $sejour->checkDaysRelative($date);
            if (CModule::getActive("brancardage") && CAppUI::conf("brancardage General see_demande_ecap", $group)) {
                $sejour->loadCurrBrancardage();
            }
            $sejour->countAlertsNotHandled("medium", "observation");
            $sejour->loadRefsOperations();
            $sejour->loadJourOp(CMbDT::date());
            if ($prescription_active) {
                $sejour->loadRefPrescriptionSejour();
                $prescription = $sejour->_ref_prescription_sejour;
                if ($prescription->_id) {
                    $prescription->loadJourOp(CMbDT::date());
                }
            }

            // Chargement des taches non effectuées
            $sejour->_count_tasks = $sejour->_count["taches_non_realisees"];

            if ($print) {
                $task               = new CSejourTask();
                $task->sejour_id    = $sejour->_id;
                $task->realise      = 0;
                $sejour->_ref_tasks = $task->loadMatchingList();
                foreach ($sejour->_ref_tasks as $_task) {
                    $_task->loadRefPrescriptionLineElement();
                }
            }

            if ($only_non_checked && !$prescription->_id) {
                unset($sejours[$sejour->_id]);
                continue;
            }

            $sejour->_count_tasks_not_created = 0;
            $sejour->_ref_tasks_not_created   = [];

            if ($prescription_active && $prescription->_id) {
                // Chargement des lignes non associées à des taches
                $where                                         = [];
                $ljoin                                         = [];
                $ljoin["element_prescription"]                 = "prescription_line_element.element_prescription_id = element_prescription.element_prescription_id";
                $ljoin["sejour_task"]                          = "sejour_task.prescription_line_element_id = prescription_line_element.prescription_line_element_id";
                $where["prescription_id"]                      = " = '$prescription->_id'";
                $where["element_prescription.rdv"]             = " = '1'";
                $where["prescription_line_element.date_arret"] = " IS NULL";
                $where["active"]                               = " = '1'";
                $where[]                                       = "sejour_task.sejour_task_id IS NULL";
                $where["child_id"]                             = " IS NULL";

                $line_element                     = new CPrescriptionLineElement();
                $sejour->_count_tasks_not_created = $line_element->countList($where, null, $ljoin);

                if ($print) {
                    $sejour->_ref_tasks_not_created = $line_element->loadList($where, null, null, null, $ljoin);
                }

                if ($only_non_checked) {
                    $prescription->countNoValideLines($user_id);
                    if ($prescription->_counts_no_valide == 0) {
                        unset($sejours[$sejour->_id]);
                        continue;
                    }
                }

                CPrescription::massAlertConfirmation($prescription);

                if (!$alert_handler) {
                    $prescription->countFastRecentModif();
                    $prescription->countFastUrgence();
                }
            }

            // Chargement des transmissions sur des cibles importantes
            $sejour->loadRefsTransmissions($cible_importante, $important, false, 1, null, $degre);
            $sejour->loadRefDossierMedical();

            $patient = $sejour->_ref_patient;
            $patient->loadRefPhotoIdentite();
            $patient->loadRefDossierMedical(false);

            if ($see_risque_pop && $lite_view
                && $patient->sexe == "f"
            ) {
                $patient->loadLastGrossesse();
            }

            $dossier_medical = $patient->_ref_dossier_medical;
            if ($dossier_medical->_id) {
                $dossiers[$dossier_medical->_id] = $dossier_medical;
            }

            if ($validation_pharm) {
                $prescription = $sejour->loadRefPrescriptionSejour();
                $prescription->loadAlertsNotHandled("medium", "prescription_pharma_modification");
                $nb_lines = 0;
                $prescription->loadRefsLinesMed();
                $prescription->loadRefsPrescriptionLineMixes();
                $nb_lines += count($prescription->_ref_prescription_lines) + count($prescription->_ref_prescription_line_mixes);

                $count_validation_pharma[$prescription->_id] = [
                    "valide" => 0,
                    "count"  => $nb_lines,
                ];
                foreach ([$prescription->_ref_prescription_lines, $prescription->_ref_prescription_line_mixes] as $_lines) {
                    foreach ($_lines as $_line) {
                        $signee = ($_line instanceof CPrescriptionLineMix) ? $_line->signature_prat : $_line->signee;

                        if (!$see_lines_no_signed && !$signee) {
                            $count_validation_pharma[$prescription->_id]["count"]--;
                        } elseif ($_line->validation_pharma) {
                            $count_validation_pharma[$prescription->_id]["valide"]++;
                        }
                    }
                }
            }
        }

        if ($prescription_active && $alert_handler) {
            CPrescription::massCountAlertsNotHandled(CMbArray::pluck($sejours, "_ref_prescription_sejour"));
            CPrescription::massCountAlertsNotHandled(CMbArray::pluck($sejours, "_ref_prescription_sejour"), "high");
        }

        $services_selected = [];
        if ($select_services_ids && !$function_id && !$praticien_id && !$discipline_id) {
            $sejours_np = [];
            foreach ($services as $_service) {
                foreach ($sejours as $_sejour) {
                    if (!$_sejour->_ref_curr_affectation->service_id) {
                        $sejours_np[$_sejour->_id] = $_sejour;
                    } elseif ($_service->_id == $_sejour->_ref_curr_affectation->service_id) {
                        $services_selected[$_sejour->_ref_curr_affectation->service_id][] = $_sejour;
                    }
                }
            }
            if (count($sejours_np)) {
                $services_selected["NP"] = $sejours_np;
            }
            $services["NP"] = new CService();
        }

        // Récupération des identifiants des dossiers médicaux
        $dossiers_id = CMbArray::pluck($sejours, "_ref_patient", "_ref_dossier_medical", "_id");

        // Suppressions des dossiers médicaux inexistants
        CMbArray::removeValue("", $dossiers);

        $_counts_allergie   = CDossierMedical::massCountAllergies($dossiers_id);
        $_counts_antecedent = CDossierMedical::massCountAntecedents($dossiers_id, $print);

        /* @var CDossierMedical[] $dossiers */
        foreach ($dossiers as $_dossier) {
            if ($print) {
                $_dossier->loadRefsAntecedents();
            }
            $_dossier->loadRefsAllergies();
            $_dossier->_count_allergies   = array_key_exists($_dossier->_id, $_counts_allergie) ? $_counts_allergie[$_dossier->_id] : 0;
            $_dossier->_count_antecedents = array_key_exists($_dossier->_id, $_counts_antecedent) ? $_counts_antecedent[$_dossier->_id] : 0;
        }

        if ($service_id == "NP") {
            $sorter = CMbArray::pluck($sejours, "_ref_patient", "nom");
            array_multisort($sorter, SORT_ASC, $sejours);
        }

        $function = new CFunctions();
        $function->load($function_id);

        $_sejour                  = new CSejour();
        $_sejour->_type_admission = $_type_admission;

        if ($count_my_patient && $my_patient && ($userCourant->isSageFemme() || $userCourant->isAideSoignant() || $userCourant->isInfirmiere(
                ) || $userCourant->isKine() || $userCourant->isPraticien())) {
            foreach ($sejours as $key_sejour => $_sejour_patient) {
                if (!count($_sejour_patient->_ref_users_sejour)) {
                    unset($sejours[$key_sejour]);
                }
            }
            foreach ($services_selected as $service_id_my_patient => $_sejours_my_patient) {
                foreach ($_sejours_my_patient as $_key_sejour => $_sejour_my_patient) {
                    if (!count($_sejour_my_patient->_ref_users_sejour)) {
                        unset($services_selected[$service_id_my_patient][$_key_sejour]);
                    }
                }
            }
        }
        if (!$count_my_patient && $my_patient) {
            $my_patient = 0;
        }

        // Chargement des visites pour les séjours courants
        $visites = CSejour::countVisitesUser($sejours, $date, $userCourant);

        // liste des spécialités médicale
        $discipline = new CDiscipline();
        $discipline->load($discipline_id);

        $listDisciplines = $discipline->loadUsedDisciplines();

        // Tri des sejours par nom de services
        if (($service_id && $service_id != "NP") || $praticien_id || $discipline_id || $function_id) {
            $lits = CMbArray::pluck($sejours, "_ref_curr_affectation", "_ref_lit");

            $sorter_service      = CMbArray::pluck($lits, "_ref_chambre", "_ref_service", "_view");
            $sorter_chambre_rank = CMbArray::pluck($lits, "_ref_chambre", "rank");
            $sorter_chambre_nom  = CMbArray::pluck($lits, "_ref_chambre", "nom");
            $sorter_lit_rank     = CMbArray::pluck($lits, "rank");
            $sorter_lit          = CMbArray::pluck($lits, "nom");

            array_walk(
                $sorter_chambre_rank,
                function ($elt, $key) use (&$sorter_service) {
                    $sorter_service[$key] .= "-" . $elt;
                }
            );

            array_walk(
                $sorter_chambre_nom,
                function ($elt, $key) use (&$sorter_service) {
                    $sorter_service[$key] .= "-" . $elt;
                }
            );

            array_walk(
                $sorter_chambre_nom,
                function ($elt, $key) use (&$sorter_service) {
                    $sorter_service[$key] .= "-" . $elt;
                }
            );

            array_walk(
                $sorter_lit_rank,
                function ($elt, $key) use (&$sorter_service) {
                    $sorter_service[$key] .= "-" . $elt;
                }
            );

            array_walk(
                $sorter_lit,
                function ($elt, $key) use (&$sorter_service) {
                    $sorter_service[$key] .= "-" . $elt;
                }
            );

            array_multisort($sorter_service, SORT_NATURAL, $sejours);
        }

        foreach ($services_selected as $key_service => &$_sejours) {
            if ($key_service == "NP") {
                continue;
            }

            $lits = CMbArray::pluck($_sejours, "_ref_curr_affectation", "_ref_lit");

            $sorter_chambre_rank = CMbArray::pluck($lits, "_ref_chambre", "rank");
            $sorter_chambre_nom  = CMbArray::pluck($lits, "_ref_chambre", "nom");
            $sorter_lit_rank     = CMbArray::pluck($lits, "rank");
            $sorter_lit          = CMbArray::pluck($lits, "nom");

            array_walk(
                $sorter_chambre_nom,
                function ($elt, $key) use (&$sorter_chambre_rank) {
                    $sorter_chambre_rank[$key] .= "-" . $elt;
                }
            );

            array_walk(
                $sorter_chambre_nom,
                function ($elt, $key) use (&$sorter_chambre_rank) {
                    $sorter_chambre_rank[$key] .= "-" . $elt;
                }
            );

            array_walk(
                $sorter_lit_rank,
                function ($elt, $key) use (&$sorter_chambre_rank) {
                    $sorter_chambre_rank[$key] .= "-" . $elt;
                }
            );

            array_walk(
                $sorter_lit,
                function ($elt, $key) use (&$sorter_chambre_rank) {
                    $sorter_chambre_rank[$key] .= "-" . $elt;
                }
            );

            array_multisort($sorter_chambre_rank, SORT_NATURAL, $_sejours);
        }

        if ($prescription_active) {
            CPrescription::massLoadLinesElementImportant(
                array_combine(
                    CMbArray::pluck($sejours, "_ref_prescription_sejour", "_id"),
                    CMbArray::pluck($sejours, "_ref_prescription_sejour")
                )
            );
        }

        //Chargement des alertes OxLabo
        $source_labo = CExchangeSource::get(
            "OxLabo" . CGroups::loadCurrent()->_id,
            CSourceHTTP::TYPE,
            false,
            "OxLaboExchange",
            false
        );
        $labo_alert_by_nda     = [];
        $new_labo_alert_by_nda = [];
        $id_sejours = [];
        if (CModule::getActive("oxLaboClient") && $source_labo->active) {
            $labo_handler          = new OxLaboClientHandler();
            $labo_alert_by_nda     = $labo_handler->getAlerteAnormalForSejours($sejours);
            $new_labo_alert_by_nda = $labo_handler->getAlertNewResultForSejours($sejours);
            foreach ($sejours as $_sejour) {
                $id_sejours[] = $_sejour->_id;
            }
        }

        $tpl_vars = [
            'service'               => $service,
            'service_id'            => $service_id,
            'sejours'               => $sejours,
            'date'                  => $date,
            'show_affectation'      => $show_affectation,
            'praticien'             => $praticien,
            'function'              => $function,
            'sejour_id'             => $sejour_id,
            'show_full_affectation' => $select_view || CValue::get("show_full_affectation"),
            'only_non_checked'      => $only_non_checked,
            'print'                 => $print,
            '_sejour'               => $_sejour,
            'ecap'                  => $ecap,
            'mode'                  => $mode,
            'services_selected'     => $services_selected,
            'visites'               => $visites,
            'listDisciplines'       => $listDisciplines,
            'discipline_id'         => $discipline_id,
            'discipline'            => $discipline,
            'my_patient'            => $my_patient,
            'count_my_patient'      => $count_my_patient,
            'select_view'           => $select_view,
            'getSourceLabo'         => $source_labo->active,
            'new_labo_alert_by_nda' => $new_labo_alert_by_nda,
            'labo_alert_by_nda'     => $labo_alert_by_nda,
            'id_sejours'            => json_encode($id_sejours),
        ];

        if ($select_view) {
            $tpl_vars = array_merge($tpl_vars, [
                'services'     => $services,
                'functions'    => $functions,
                'praticiens'   => $praticiens,
                'function_id'  => $function_id,
                'praticien_id' => $praticien_id,
            ]);
        }

        if ($sejour_id) {
            $tpl_vars = array_merge($tpl_vars, [
                'board'     => $board,
                'lite_view' => $lite_view,
            ]);

            // Rafraichissement d'un séjour
            $sejour = reset($sejours);
            $sejour->loadRefsAffectations();
            $sejour->loadRefCurrAffectation()->loadRefLit()->loadRefChambre()->loadRefService();

            $affectation_service->prepareCleanup($sejour->_ref_affectations, $hotellerie_active, $date);

            // On assigne le service_id seulement dans le cas où on a la sélection multiple de services
            if (count($services_ids)) {
                $service_id = $sejour->_ref_curr_affectation->_id ?
                    $sejour->_ref_curr_affectation->service_id :
                    $sejour->_ref_last_affectation->service_id;

                $tpl_vars ['service_id'] = $service_id;
            }

            if ($validation_pharm) {
                $tpl_vars ['count_validation_pharma'] = $count_validation_pharma;
            }

            $tpl_vars ['sejour'] = $sejour;
            $tpl = 'inc_vw_sejour';
        } elseif ($refresh) {
            // Raffraichissement de la liste des sejours
            $tpl = 'inc_vw_sejours_global';
        } else {
            // Affichage de la liste des sejours
            $tpl = 'vw_sejours';
        }

        $this->renderSmarty($tpl, $tpl_vars);
    }
}
