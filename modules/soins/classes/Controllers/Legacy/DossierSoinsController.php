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
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Core\Module\CModule;
use Ox\Interop\Imeds\CImeds;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CChambre;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\Mpm\CPrescriptionLineMix;
use Ox\Mediboard\Mpm\CPrescriptionLineMixItem;
use Ox\Mediboard\OxLaboClient\OxLaboClientHandler;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanSoins\CAdministration;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Soins\DossierSoinsService;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceHTTP;

/**
 * Class DossierSoinsController
 * @package Ox\Mediboard\Soins\Controllers\Legacy
 */
class DossierSoinsController extends CLegacyController
{
    /**
     *  Copié du script "ajax_vw_perop_administrations.php" adapté pour une opération particulière
     *
     *  Charge les événements perop pour une intervention
     * @throws Exception
     */
    public function ajaxViewPeropOperation(): void
    {
        $this->checkPermRead();

        $prescription_id      = CView::get("prescription_id", "ref class|CPrescription");
        $sejour_id            = CView::get("sejour_id", "ref class|CSejour");
        $operation_id         = CView::get("operation_id", "ref class|COperation");
        $show_administrations = CView::get("show_administrations", "bool default|0");

        CView::checkin();

        $sejour     = CSejour::find($sejour_id);
        $operation  = COperation::findOrFail($operation_id);
        $operations = $sejour->loadRefsOperations(["annulee" => "= '0'"]);

        CAccessMedicalData::logAccess($sejour);

        // Tri des gestes et administrations perop par ordre chronologique
        $perops = [];

        // Chargement des administrations
        $administrations              = CAdministration::getPerop($prescription_id, true, $operation->_id);
        $count_administrations_gestes = 0;

        foreach ($administrations as $_adm) {
            $_adm->loadRefsFwd();
            $object = $_adm->_ref_object;

            if ($object instanceof CPrescriptionLineMedicament || $object instanceof CPrescriptionLineMixItem) {
                $_produit = $object->_ref_produit;
                $_produit->loadRapportUnitePriseByCIS($object);
                $_produit->updateRatioMassique();

                if ($_produit->_ratio_mg) {
                    $_adm->_quantite_mg = $_adm->quantite / $_produit->_ratio_mg;
                }

                [$unite_lt, $qte_lt] = CPrescriptionLineMedicament::computeQteUnitLTPerop(
                    $object,
                    $_adm->quantite
                );

                $_adm->_ref_object->_unite_livret = $unite_lt;
                $_adm->_ref_object->_qte_livret   = $qte_lt;
            }

            $section              = CAdministration::getSectionPerop($operation->_id, $_adm->dateTime);
            $_adm->_perop_section = $section;

            $perops[$_adm->dateTime][$_adm->_guid] = $_adm;
            $count_administrations_gestes++;
        }

        if ($sejour->_ref_prescription_sejour && $sejour->_ref_prescription_sejour->_id) {
            // Chargements des perfusions pour afficher les poses et les retraits
            $prescription_line_mix                  = new CPrescriptionLineMix();
            $prescription_line_mix->prescription_id = $prescription_id;
            $prescription_line_mix->perop           = 1;
            /** @var CPrescriptionLineMix[] $mixes */
            $mixes = $prescription_line_mix->loadMatchingList();

            CStoredObject::massLoadFwdRef($mixes, "praticien_id");

            foreach ($mixes as $_mix) {
                $_mix->loadRefPraticien();
                $_mix->loadRefsLines();
                if ($_mix->date_pose && $_mix->time_pose) {
                    $section = CAdministration::getSectionPerop($operation->_id, $_mix->_pose);

                    $_mix->_perop_section                         = $section;
                    $perops[$section][$_mix->_pose][$_mix->_guid] = $_mix;
                }
                if ($_mix->date_retrait && $_mix->time_retrait) {
                    $section = CAdministration::getSectionPerop($operation->_id, $_mix->_retrait);

                    $_mix->_perop_section                            = $section;
                    $perops[$section][$_mix->_retrait][$_mix->_guid] = $_mix;
                }
                $count_administrations_gestes++;
            }
            ksort($perops);
        }

        // Load the Perop gestures
        $anesths_perop = $operation->loadRefsAnesthPerops();

        if (!$show_administrations) {
            foreach ($anesths_perop as $_anesth_perop) {
                $_anesth_perop->updateFormFields();
                $_anesth_perop->loadRefUser();

                $section = CAdministration::getSectionPerop($operation->_id, $_anesth_perop->datetime);

                $_anesth_perop->_perop_section = $section;

                $perops[$_anesth_perop->datetime][$_anesth_perop->_guid] = $_anesth_perop;
                $count_administrations_gestes++;
            }
        }
        $this->renderSmarty(
            "inc_vw_perop_administrations",
            [
                "perops"                       => $perops,
                "operation"                    => $operation,
                "operations"                   => $operations,
                "count_administrations_gestes" => $count_administrations_gestes,
                "show_administrations"         => $show_administrations,
                "prescription_id"              => $prescription_id,
            ]
        );
    }

    /**
     * @throws CMbModelNotFoundException
     * @throws Exception
     */
    public function reloadPatientBanner()
    {
        $this->checkPermRead();

        $patient_id = CView::get("patient_id", "ref class|CPatient");

        CView::checkin();

        $patient = CPatient::findOrFail($patient_id);
        $patient->loadRefDossierMedical();
        $patient->loadRefLatestConstantes();

        $this->renderSmarty(
            "inc_infos_patients_soins",
            [
                "patient" => $patient,
            ]
        );
    }

    /**
     * Charge le dossier de séjour - Remplace le script ajax_vw_dossier_sejour
     *
     * @throws Exception
     */
    public function viewDossierSejour(): void
    {
        $this->checkPermRead();

        $sejour_id           = CView::get("sejour_id", "ref class|CSejour");
        $date                = CView::get("date", "date default|now");
        $defined_default_tab =
            "dossier_traitement" . (CAppUI::gconf("soins Other vue_condensee_dossier_soins") ? "_compact" : "");

        if (CAppUI::gconf("soins dossier_soins tab_prescription_med") && CMediusers::get()->isPraticien()) {
            $defined_default_tab = "prescription_sejour";
        }

        $default_tab       = CView::get("default_tab", "str default|$defined_default_tab");
        $popup             = CView::get("popup", "bool default|0");
        $modal             = CView::get("modal", "bool default|0");
        $operation_id      = CView::get("operation_id", "ref class|COperation");
        $mode_pharma       = CView::get("mode_pharma", "bool default|0");
        $mode_protocole    = CView::get("mode_protocole", "bool default|0", true);
        $type_prescription = CView::get("type_prescription", "enum list|pre_admission|sejour|sortie default|sejour");
        $line_guid_open    = CView::get("line_guid_open", "str");

        CView::checkin();

        $service = new DossierSoinsService($sejour_id, $date, $operation_id);

        $service->loadDossierSoins();

        $form_tabs = $service->form_tabs;

        $isImedsInstalled = CModule::getActive("dPImeds") && CImeds::getTagCIDC(CGroups::loadCurrent());

        $late_objectifs = $service->getLateObjectifsSoins();

        $source_labo = CExchangeSource::get(
            "OxLabo" . CGroups::loadCurrent()->_id,
            CSourceHTTP::TYPE,
            false,
            "OxLaboExchange",
            false
        );

        $this->renderSmarty(
            "inc_dossier_sejour",
            [
                "sejour"                  => $service->getSejour(),
                "patient"                 => $service->getPatient(),
                "date"                    => $date,
                "date_plan_soins"         => $service->getDatePlanSoins(),
                "default_tab"             => $default_tab,
                "popup"                   => $popup,
                "modal"                   => $modal,
                "operation_id"            => $operation_id,
                "mode_pharma"             => $mode_pharma,
                "is_praticien"            => CAppUI::$user->isPraticien(),
                "mode_protocole"          => $mode_protocole,
                "operation"               => $service->getOperation(),
                "form_tabs"               => $form_tabs,
                "late_objectifs"          => $late_objectifs,
                "isImedsInstalled"        => $isImedsInstalled,
                "isPrescriptionInstalled" => $service->isPrescriptionInstalled,
                "type_prescription"       => $type_prescription,
                "line_guid_open"          => $line_guid_open,
                "getSourceLabo"           => $source_labo->active ? true : false,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function viewBilanPrescription(): void
    {
        $this->checkPermRead();
        $m    = $GLOBALS['m'];
        $user = CMediusers::get();

        $praticien_id      = CView::get("prat_bilan_id", "ref class|CMediusers default|" . $user->_id);
        $signee            = CView::get("signee", "bool default|0");
        $date_min          = CView::get("_date_entree_prevue", "date default|" . CMbDT::date());
        $date_max          = CView::get("_date_sortie_prevue", "date default|" . CMbDT::date());
        $type_prescription = CView::get("type_prescription", "str default|sejour");

        CView::checkin();

        // Chargement de la liste des praticiens
        $mediuser   = new CMediusers();
        $praticiens = $mediuser->loadPraticiens();

        /* Handle the list of mediusers for the view displayed in the board module */
        if ($m === 'dPboard') {
            $board_access = CAppUI::pref("allow_other_users_board");
            if ($user->isProfessionnelDeSante() && $board_access == 'only_me') {
                $praticiens = [$user->_id => $user];
            } elseif ($user->isProfessionnelDeSante() && $board_access == 'same_function') {
                $praticiens = $mediuser->loadPraticiens(PERM_READ, $user->function_id);
            } elseif ($user->isProfessionnelDeSante() && $board_access == 'write_right') {
                $praticiens = $mediuser->loadPraticiens(PERM_EDIT);
            }
        }

        if (!$praticien_id && $user->isPraticien()) {
            $praticien_id = $user->_id;
        }

        $sejour                      = new CSejour();
        $sejour->_date_entree_prevue = $date_min;
        $sejour->_date_sortie_prevue = $date_max;

        $this->renderSmarty(
            "vw_bilan_prescription",
            [
                "praticiens"        => $praticiens,
                "praticien_id"      => $praticien_id,
                "signee"            => $signee,
                "sejour"            => $sejour,
                "type_prescription" => $type_prescription,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function viewIndexSejour(): void
    {
        $m = $GLOBALS["m"];

        // Redirection pour gérer le cas ou le volet par defaut est l'autre affichage des sejours
        if (CAppUI::pref("vue_sejours") === "global" && $m === "soins") {
            CAppUI::redirect("m=soins&tab=vwSejours");
        }

        CAppUI::requireModuleFile("dPhospi", "inc_vw_affectations");

        $this->checkPermRead();
        $group = CGroups::loadCurrent();

        // Filtres
        $current_m            = CView::get("current_m", "str default|$m");
        $date                 = CView::get("date", "date", true);
        $mode                 = CView::get("mode", "num default|0", true);
        $service_id           = CView::get("service_id", "str", true);
        $praticien_id         = CView::get("praticien_id", "str", true);
        $type_admission       = CView::get("type", "str", true);
        $function_id          = CView::get("func_id", "ref class|CFunctions", true);
        $discipline_id        = CView::get("discipline_id", "ref class|CDiscipline", true);
        $my_patient           = CView::get("my_patient", "str", true);
        $services_ids         = CView::get("services_ids", "str", true);
        $order_col_sejour     = CView::get(
            "order_col_sejour",
            "enum list|praticien_id|patient_id|_entree|_date_consult|order_sent default|patient_id",
            true
        );
        $GLOBALS["current_m"] = $current_m;
        $order_way_sejour     = CView::get("order_way_sejour", "enum list|ASC|DESC default|ASC", true);

        // Chargement de l'utilisateur courant
        $userCourant = CMediusers::get();

        $_is_praticien = $userCourant->isPraticien();
        $_is_anesth    = $userCourant->isAnesth();

        // Préselection du contexte
        if (!$praticien_id && !$function_id && !$discipline_id) {
            switch (CAppUI::pref("preselect_me_care_folder")) {
                case "1":
                    if ($_is_praticien) {
                        $praticien_id = $userCourant->user_id;
                    }
                    break;
                case "2":
                    $function_id = $userCourant->function_id;
                    break;
                case "3":
                    if ($_is_praticien) {
                        $discipline_id = $userCourant->discipline_id;
                    }
                    break;
                default:
            }
        }

        if ($praticien_id == 'none') {
            $praticien_id = '';
        }

        $changeSejour = @CView::get("service_id", "ref class|CService") ||
            @CView::get("praticien_id", "ref class|CMediusers");
        $changeSejour = $changeSejour || (
                !$service_id &&
                is_countable($services_ids) &&
                !count($services_ids) &&
                !$praticien_id
            );

        if ($changeSejour) {
            $sejour_id = null;
            CView::setSession("sejour_id");
        } else {
            $sejour_id = CView::get("sejour_id", "ref class|CSejour", true);
        }

        if (CAppUI::conf("soins Sejour select_services_ids", $group)) {
            $services_ids = CService::getServicesIdsPref($services_ids);
            if ($services_ids) {
                $service_id = null;
            }

            if ($discipline_id || $function_id) {
                $services_ids = [];
            }
        } else {
            $services_ids = [];
        }

        CView::checkin();

        $ds = CSQLDataSource::get("std");

        // récuperation du service par défaut dans les préférences utilisateur
        $default_services_id = CAppUI::pref("default_services_id", "{}");

        // Ne pas prendre en compte le service pour le filtre sur discipline ou spécialité
        if ($function_id || $discipline_id) {
            $default_services_id = "";
        }

        // Récuperation du service à afficher par défaut (on prend le premier s'il y en a plusieurs)
        [$default_service_id, $service_id] = $this->getDefaultServiceId(
            $default_services_id,
            $group,
            $service_id,
            $praticien_id
        );

        if (!$date) {
            $date = CMbDT::date();
        }

        $alert_handler = HandlerManager::isObjectHandlerActive('CPrescriptionAlerteHandler');

        $prescription_active = CModule::getActive("dPprescription");

        $tab_sejour = [];

        // Récupération de la liste des services
        $where   = [
            "externe"   => "= '0'",
            "cancelled" => "= '0'",
        ];
        $service = new CService();

        if ($_is_praticien) {
            $services = $service->loadGroupList($where);
        } else {
            $services = $service->loadListWithPerms(PERM_READ, $where);
        }

        // Récupération du service à ajouter/éditer
        $totalLits = 0;

        // A passer en variable de configuration
        $heureLimit = "16:00:00";

        $groupSejourNonAffectes = [];

        // Chargement de la liste de praticiens
        $prat       = new CMediusers();
        $praticiens = $prat->loadPraticiens(PERM_READ);

        /* Handle the list of mediusers for the view displayed in the board module */
        if ($current_m === 'dPboard') {
            $board_access = CAppUI::pref("allow_other_users_board");
            if ($userCourant->isProfessionnelDeSante() && $board_access == "only_me") {
                $praticiens = [$userCourant->_id => $userCourant];
            } elseif ($userCourant->isProfessionnelDeSante() && $board_access == "same_function") {
                $praticiens = $prat->loadPraticiens(PERM_READ, $userCourant->function_id);
            } elseif ($userCourant->isProfessionnelDeSante() && $board_access == "write_right") {
                $praticiens = $prat->loadPraticiens(PERM_EDIT);
            }
        }

        $count_my_patient = 0;

        // Chargement du praticien
        $praticien = new CMediusers();
        if ($praticien_id) {
            $praticien->load($praticien_id);
        }

        $anesth        = new CMediusers();
        $anesthesistes = array_keys($anesth->loadAnesthesistes());

        $ids_all_sejours = [];
        // Si seulement le praticien est indiqué
        if (($praticien_id || $function_id || $discipline_id) && !$service_id && !count($services_ids)) {
            $sejour            = new CSejour();
            $where             = [];
            $ljoin             = [];
            $where["group_id"] = $ds->prepare("= ?", $group->_id);
            $whereUser         = "";
            if ($praticien->_id) {
                $whereUser = $praticien->getUserSQLClause();
            }

            if ($praticien->isAnesth()) {
                $ljoin["operations"] = "operations.sejour_id = sejour.sejour_id";
                $ljoin["plagesop"]   = "operations.plageop_id = plagesop.plageop_id";
                $where[100]          = "operations.anesth_id $whereUser OR 
                (operations.anesth_id IS NULL AND plagesop.anesth_id $whereUser)
                OR sejour.praticien_id $whereUser";
            } elseif ($function_id || $discipline_id) {
                $where["sejour.praticien_id"] = CSQLDataSource::prepareIn(array_keys($praticiens), $praticien_id);
            } else {
                $where["sejour.praticien_id"] = $whereUser;
            }

            $where["sejour.entree"] = $ds->prepare(" <= ", "$date 23:59:59");
            $where["sejour.sortie"] = $ds->prepare(" >= ", "$date 00:00:00");
            $where["annule"]        = $ds->prepare(" = '0'");
            $where[]                = $type_admission ?
                "type " . $ds->prepare(" = ?", '$type_admission') :
                "type " . $ds->prepareNotIn(CSejour::getTypesSejoursUrgence()) . " AND type " . $ds->prepare(
                    " != 'exte'"
                );
            if ($function_id || $discipline_id) {
                $ljoin["users_mediboard"]    = "sejour.praticien_id = users_mediboard.user_id";
                $ljoin["secondary_function"] = "sejour.praticien_id = secondary_function.user_id";

                if ($function_id) {
                    $where[101] = "$function_id IN (users_mediboard.function_id, secondary_function.function_id)";
                } else {
                    $where[102] = "users_mediboard.discipline_id" . $ds->prepare(" = ?", $discipline_id);
                }
            }

            if ($praticien->isAnesth() || $function_id || $discipline_id) {
                $sejours = $sejour->loadList($where, null, null, "sejour.sejour_id", $ljoin);
            } else {
                $sejours = $sejour->loadList($where);
            }

            if (CAppUI::gconf("dPplanningOp CSejour use_prat_aff")) {
                $ljoin["affectation"] = "affectation.sejour_id = sejour.sejour_id";

                if ($praticien->isAnesth()) {
                    $where[100] = "operations.anesth_id $whereUser OR 
                    (operations.anesth_id IS NULL AND plagesop.anesth_id $whereUser)
                OR affectation.praticien_id $whereUser";
                } elseif ($function_id || $discipline_id) {
                    unset($ljoin["users_mediboard"]);
                    unset($ljoin["secondary_function"]);
                    $ljoin["users_mediboard"]    = "affectation.praticien_id = users_mediboard.user_id";
                    $ljoin["secondary_function"] = "affectation.praticien_id = secondary_function.user_id";
                } else {
                    $where["affectation.praticien_id"] = $where["sejour.praticien_id"];
                    unset($where["sejour.praticien_id"]);
                }

                $sejours += $sejour->loadList($where, null, null, "sejour.sejour_id", $ljoin);
            }

            $dtnow = CMbDT::dateTime();
            $dnow  = CMbDT::date();

            foreach ($sejours as $_sejour) {
                $ids_all_sejours[$_sejour->_id] = $_sejour;
                $count_my_patient               += count($_sejour->loadRefsUserSejour($userCourant, $date, $mode));
                /* @var CSejour $_sejour */
                if ($_is_anesth || ($_is_praticien && $_sejour->praticien_id == $userCourant->user_id)) {
                    $tab_sejour[$_sejour->_id] = $_sejour;
                }
                $affectation                    = new CAffectation();
                $where                          = [];
                $where["affectation.sejour_id"] = $ds->prepare(" = ?", $_sejour->_id);
                $where["affectation.entree"]    = $ds->prepare("<=?", "$date 23:59:59");
                $where["affectation.sortie"]    = $ds->prepare(">=?", "$date 00:00:00");
                $ljoin                          = [];
                $complement                     = "";
                if ($date == $dnow) {
                    $ljoin["sejour"] = "affectation.sejour_id = sejour.sejour_id";
                    $complement      = ($mode == "0" ? "OR " : "") .
                        "(sejour.sortie_reelle >= '$dtnow' AND affectation.sortie >= '$dtnow')";
                }

                if ($complement || $mode === "0") {
                    $where[] = ($mode == "0" ? "affectation.effectue = '0' " : "") . $complement;
                }

                $affectations = $affectation->loadList($where, null, null, null, $ljoin);

                if (count($affectations) >= 1) {
                    foreach ($affectations as $_affectation) {
                        /* @var CAffectation $_affectation */
                        $_affectation->loadRefsAffectations();
                        $this->cacheLit($_affectation);
                    }
                } else {
                    $_sejour->loadRefsPrescriptions();
                    $_sejour->loadRefPatient();
                    $_sejour->loadRefPraticien();
                    $_sejour->loadLastAutorisationPermission();

                    $_sejour->_ref_praticien->loadRefFunction();
                    $_sejour->loadNDA();
                    $sejoursParService["NP"][$_sejour->_id] = $_sejour;
                }
            }
        }
        $sejoursParService = array_key_exists("sejoursParService", $GLOBALS) ? $GLOBALS["sejoursParService"] : [];
        $all_sejours       = array_key_exists("all_sejours", $GLOBALS) ? $GLOBALS["all_sejours"] : [];

        foreach ($sejoursParService as $key => $_service) {
            if ($key != "NP") {
                CMbArray::pluckSort($_service->_ref_chambres, SORT_ASC, "nom");
                foreach ($_service->_ref_chambres as $_chambre) {
                    foreach ($_chambre->_ref_lits as $_lit) {
                        foreach ($_lit->_ref_affectations as $_affectation) {
                            $_affectation->loadRefsAffectations();
                            $_affectation->loadRefSejour();
                            $_sejour                        = $_affectation->_ref_sejour;
                            $ids_all_sejours[$_sejour->_id] = $_sejour;
                            if ($_is_anesth || ($_is_praticien && $_sejour->praticien_id == $userCourant->user_id)) {
                                $tab_sejour[$_sejour->_id] = $_sejour;
                            }
                            $this->loadSejour(
                                $_sejour,
                                $_affectation,
                                $temp_count,
                                $userCourant,
                                $date,
                                $mode,
                                $alert_handler
                            );
                        }
                    }
                }
            }
        }

        // Tri des sejours par nom de services
        $sejoursParService = CMbArray::ksortByArray($sejoursParService, array_merge(array_keys($services), ['NP']));

        // Récuperation du sejour sélectionné
        $sejour = new CSejour();
        $sejour->load($sejour_id);

        if ($service_id || count($services_ids)) {
            // Chargement des séjours à afficher
            if ($service_id && !in_array($service_id, $services_ids)) {
                $services_ids[] = $service_id;
            }

            foreach ($services_ids as $_service_id) {
                if ($_service_id == "NP") {
                    // Liste des patients à placer

                    // Admissions de la veille
                    $dayBefore = CMbDT::date("-1 days", $date);
                    $where     = [
                        "entree_prevue" => $ds->prepareBetween("$dayBefore 00:00:00", "$date 00:00:00"),
                        "type"          => $type_admission ?
                            $ds->prepare(" = ?", $type_admission) :
                            $ds->prepare("!= 'exte'"),
                        "annule"        => $ds->prepare("= '0'"),
                    ];

                    $groupSejourNonAffectes["veille"] = loadSejourNonAffectes($where, null, $praticien_id);

                    // Admissions du matin
                    $where = [
                        "entree_prevue" => $ds->prepareBetween(
                            "$date 00:00:00",
                            "$date " . CMbDT::time("-1 second", $heureLimit)
                        ),
                        "type"          => $type_admission ?
                            $ds->prepare(" = ?", $type_admission) :
                            $ds->prepare("!= 'exte'"),
                        "annule"        => $ds->prepare("= '0'"),
                    ];

                    $groupSejourNonAffectes["matin"] = loadSejourNonAffectes($where, null, $praticien_id);

                    // Admissions du soir
                    $where = [
                        "entree_prevue" => $ds->prepareBetween("$date $heureLimit", "$date 23:59:59"),
                        "type"          => $type_admission ?
                            $ds->prepare(" = '$type_admission'") :
                            $ds->prepare("!= 'exte'"),
                        "annule"        => $ds->prepare("= '0'"),
                    ];

                    $groupSejourNonAffectes["soir"] = loadSejourNonAffectes($where, null, $praticien_id);

                    // Admissions antérieures
                    $twoDaysBefore = CMbDT::date("-2 days", $date);
                    $where         = [
                        "entree_prevue" => $ds->prepare("<= ?", "$twoDaysBefore 23:59:59"),
                        "sortie_prevue" => $ds->prepare(">= ?", "$date 00:00:00"),
                        "annule"        => $ds->prepare("= '0'"),
                        "type"          => $type_admission ?
                            $ds->prepare(" = ?", $type_admission) :
                            $ds->prepare("!= 'exte'"),
                    ];

                    $groupSejourNonAffectes["avant"] = loadSejourNonAffectes($where, null, $praticien_id);

                    foreach ($groupSejourNonAffectes as $moment => $_groupSejourNonAffectes) {
                        CMbArray::pluckSort($groupSejourNonAffectes[$moment], SORT_ASC, "entree_prevue");
                    }

                    foreach ($groupSejourNonAffectes as $sejours_by_moment) {
                        foreach ($sejours_by_moment as $_sejour) {
                            $patient            = $_sejour->loadRefPatient();
                            $patient->_homonyme = count($patient->getPhoning($date));
                            if (
                                ($_is_praticien || $_is_anesth) &&
                                (($_sejour->praticien_id == $userCourant->user_id) || $_is_anesth)
                            ) {
                                $tab_sejour[$_sejour->_id] = $_sejour;
                            }

                            $ids_all_sejours[$_sejour->_id] = $_sejour;
                        }
                    }
                    $service = new CService();
                } else {
                    $service = new CService();
                    $service->load($_service_id);
                    loadServiceComplet($service, $date, $mode, $praticien_id, $type_admission);
                    loadAffectationsPermissions($service, $date, $mode);
                }

                if ($service->_id) {
                    foreach ($service->_ref_chambres as $_chambre) {
                        foreach ($_chambre->_ref_lits as $_lits) {
                            CStoredObject::massLoadFwdRef($_lits->_ref_affectations, "parent_affectation_id");
                            foreach ($_lits->_ref_affectations as $_affectation) {
                                $_affectation->loadRefParentAffectation();
                                if ($_affectation->_ref_sejour->annule) {
                                    unset($_lits->_ref_affectations[$_affectation->_id]);
                                    continue;
                                }
                                if (
                                    $_is_anesth ||
                                    (
                                        $_is_praticien &&
                                        $_affectation->_ref_sejour->praticien_id == $userCourant->user_id
                                    )
                                ) {
                                    $tab_sejour[$_affectation->_ref_sejour->_id] = $_affectation->_ref_sejour;
                                }
                                $sejour                        = $_affectation->_ref_sejour;
                                $ids_all_sejours[$sejour->_id] = $sejour;
                                $this->loadSejour(
                                    $sejour,
                                    $_affectation,
                                    $count_my_patient,
                                    $userCourant,
                                    $date,
                                    $mode,
                                    $alert_handler
                                );
                            }
                        }
                    }

                    $service->loadRefsAffectationsCouloir($date, $mode, true);

                    $_sejours  = CStoredObject::massLoadFwdRef($service->_ref_affectations_couloir, "sejour_id");
                    $_patients = CStoredObject::massLoadFwdRef($_sejours, "patient_id");
                    CStoredObject::massLoadBackRefs($_patients, "bmr_bhre");

                    foreach ($service->_ref_affectations_couloir as $_affectation) {
                        $_affectation->loadRefSejour();
                        if (
                            $_affectation->_ref_sejour->annule ||
                            (
                                $praticien_id &&
                                $_affectation->_ref_sejour->praticien_id != $praticien_id)
                        ) {
                            unset($service->_ref_affectations_couloir[$_affectation->_id]);
                            continue;
                        }
                        if (
                            $_is_anesth ||
                            (
                                $_is_praticien &&
                                $_affectation->_ref_sejour->praticien_id == $userCourant->user_id
                            )
                        ) {
                            $tab_sejour[$_affectation->_ref_sejour->_id] = $_affectation->_ref_sejour;
                        }
                        $sejour                        = $_affectation->_ref_sejour;
                        $ids_all_sejours[$sejour->_id] = $sejour;
                        $this->loadSejour(
                            $sejour,
                            $_affectation,
                            $count_my_patient,
                            $userCourant,
                            $date,
                            $mode,
                            $alert_handler
                        );
                    }
                }

                $sejoursParService[$service->_id] = $service;
            }
        }

        $see_my_patient = $count_my_patient && $my_patient && (
                $userCourant->isSageFemme() ||
                $userCourant->isAideSoignant() ||
                $userCourant->isInfirmiere() ||
                $userCourant->isKine() ||
                $userCourant->isPraticien()
            );

        $sejours_show_anesth_interv = [];
        foreach ($sejoursParService as $key => $_service) {
            if ($key != "NP") {
                foreach ($_service->_ref_chambres as $_chambre) {
                    foreach ($_chambre->_ref_lits as $key_lit => $_lit) {
                        foreach ($_lit->_ref_affectations as $key_affectation => $_affectation) {
                            $_sejour            = $_affectation->loadRefSejour();
                            $patient            = $_sejour->loadRefPatient();
                            $patient->_homonyme = count($patient->getPhoning($date));
                            if (!count($_sejour->_ref_users_sejour) && $see_my_patient) {
                                unset($_lit->_ref_affectations[$key_affectation]);
                            } else {
                                $sejours_show_anesth_interv[$_sejour->_id] = $_sejour;
                            }
                        }
                        if (!count($_lit->_ref_affectations) && $see_my_patient) {
                            unset($_chambre->_ref_lits[$key_lit]);
                        }
                    }
                }
            } else {
                foreach ($_service as $_sejour) {
                    if (!count($_sejour->_ref_users_sejour) && $see_my_patient) {
                        unset($_lit->_ref_affectations[$key_affectation]);
                    } else {
                        $sejours_show_anesth_interv[$_sejour->_id] = $_sejour;
                    }
                }
            }
        }

        $operations = CStoredObject::massLoadBackRefs($sejours_show_anesth_interv, "operations", "date ASC");
        if (is_array($operations) && count($operations)) {
            $plages_ops = CStoredObject::massLoadFwdRef($operations, "plageop_id");
            CStoredObject::massLoadFwdRef($operations, "chir_id");
            CStoredObject::massLoadFwdRef($operations, "anesth_id");
            CStoredObject::massLoadFwdRef($plages_ops, "anesth_id");
        }

        foreach ($sejours_show_anesth_interv as $other_sejour) {
            $anesths = [];
            $chirs   = [];
            foreach ($other_sejour->loadRefsOperations() as $_interv) {
                if (!isset($chirs[$_interv->chir_id])) {
                    $_interv->loadRefPraticien()->loadRefFunction();
                    $chirs[$_interv->chir_id] = 1;
                }
                $_interv->loadRefAnesth()->loadRefFunction();
                if (!isset($anesths[$_interv->_ref_anesth->_id])) {
                    $anesths[$_interv->_ref_anesth->_id] = 1;
                } else {
                    $_interv->_ref_anesth = null;
                }
            }
        }

        if ($prescription_active && $alert_handler) {
            CPrescription::massCountAlertsNotHandled(CMbArray::pluck($all_sejours, "_ref_prescriptions", "sejour"));
            CPrescription::massCountAlertsNotHandled(
                CMbArray::pluck($all_sejours, "_ref_prescriptions", "sejour"),
                "high"
            );
        }

        if ($prescription_active) {
            CPrescription::massLoadLinesElementImportant(
                array_combine(
                    CMbArray::pluck($all_sejours, "_ref_prescriptions", "sejour", "_id"),
                    CMbArray::pluck($all_sejours, "_ref_prescriptions", "sejour")
                )
            );
        }

        if (!$count_my_patient && $my_patient) {
            $my_patient = 0;
        }

        // Chargement des visites pour les séjours courants
        $visites = CSejour::countVisitesUser($tab_sejour, $date, $userCourant);

        $can_view_dossier_medical =
            CModule::getCanDo('dPcabinet')->edit ||
            CModule::getCanDo('dPbloc')->edit ||
            CModule::getCanDo('dPplanningOp')->edit ||
            $userCourant->isFromType(["Infirmière"]);

        $function  = new CFunctions();
        $functions = $function->loadSpecialites();

        // liste des spécialités médicale
        $listDisciplines = new CDiscipline();
        $listDisciplines = $listDisciplines->loadUsedDisciplines();

        //Chargement des alertes OxLabo
        $labo_alert_by_nda     = [];
        $new_labo_alert_by_nda = [];
        $source_labo           = CExchangeSource::get(
            "OxLabo" . CGroups::loadCurrent()->_id,
            CSourceHTTP::TYPE,
            false,
            "OxLaboExchange",
            false
        );
        $id_sejours            = [];
        if (CModule::getActive("oxLaboClient") && $source_labo->active) {
            $labo_handler          = new OxLaboClientHandler();
            $labo_alert_by_nda     = $labo_handler->getAlerteAnormalForSejours($ids_all_sejours);
            $new_labo_alert_by_nda = $labo_handler->getAlertNewResultForSejours($ids_all_sejours);
            foreach ($ids_all_sejours as $_sejour) {
                $id_sejours[] = $_sejour->_id;
            }
        }

        $this->renderSmarty(
            "vw_idx_sejour",
            [
                "default_service_id"       => $default_service_id,
                "_is_praticien"            => $_is_praticien,
                "anesthesistes"            => $anesthesistes,
                "praticiens"               => $praticiens,
                "praticien_id"             => $praticien_id,
                "function_id"              => $function_id,
                "discipline_id"            => $discipline_id,
                "object"                   => $sejour,
                "mode"                     => $mode,
                "totalLits"                => $totalLits,
                "date"                     => $date,
                "isImedsInstalled"         => (CModule::getActive("dPImeds") && CImeds::getTagCIDC($group)),
                "can_view_dossier_medical" => $can_view_dossier_medical,
                "demain"                   => CMbDT::date("+ 1 day", $date),
                "services"                 => $services,
                "sejoursParService"        => $sejoursParService,
                "service_id"               => $service_id,
                "groupSejourNonAffectes"   => $groupSejourNonAffectes,
                "visites"                  => $visites,
                "my_patient"               => $my_patient,
                "count_my_patient"         => $count_my_patient,
                "services_ids"             => $services_ids,
                "listDisciplines"          => $listDisciplines,
                "listFuncs"                => $functions,
                "current_m"                => ($current_m) ?: null,
                "order_col_sejour"         => $order_col_sejour,
                "order_way_sejour"         => $order_way_sejour,
                "type_admission"           => $type_admission,
                "labo_alert_by_nda"        => $labo_alert_by_nda,
                "new_labo_alert_by_nda"    => $new_labo_alert_by_nda,
                "id_sejours"               => json_encode($id_sejours),
            ]
        );
    }

    /**
     * Divers chargement lié au séjour
     *
     * @param CSejour      $sejour           Séjour
     * @param CAffectation $_affectation     Affectation
     * @param int          $count_my_patient Nombre de séjours
     * @param CMediusers   $userCourant      Utilisateur courant
     * @param string       $date             Date des séjours chargés
     * @param string       $mode             Type de vue
     * @param bool         $alert_handler    Gestion manuelle des alertes
     *
     * return void
     *
     * @throws Exception
     */
    private function loadSejour(
        CSejour &$sejour,
        CAffectation &$_affectation,
        int &$count_my_patient,
        CMediusers $userCourant,
        string $date,
        string $mode,
        bool $alert_handler
    ): void {
        $all_sejours = array_key_exists("all_sejours", $GLOBALS) ? $GLOBALS["all_sejours"] : [];
        if ($sejour->_id) {
            $all_sejours[$sejour->_id] = $sejour;
        }
        $sejour->loadRefPraticien();
        if (!$sejour->_NDA) {
            $sejour->loadNDA();
        }
        $sejour->loadRefPatient()->updateBMRBHReStatus($sejour);
        $patient            = $sejour->_ref_patient;
        $patient->_homonyme = count($patient->getPhoning($date));
        $sejour->loadRefsPrescriptions();
        $sejour->loadLastAutorisationPermission();
        $sejour->_ref_praticien->loadRefFunction();
        $sejour->countAlertsNotHandled("medium", "observation");
        $count_my_patient += count($sejour->loadRefsUserSejour($userCourant, $date, $mode));
        $_affectation->loadRefsAffectations();
        if ($_affectation->_ref_sejour->_ref_prescriptions) {
            if (array_key_exists('sejour', $_affectation->_ref_sejour->_ref_prescriptions)) {
                $prescription_sejour = $_affectation->_ref_sejour->_ref_prescriptions["sejour"];
                $prescription_sejour->countNoValideLines();
                CPrescription::massAlertConfirmation($prescription_sejour);

                if (!$alert_handler) {
                    $prescription_sejour->countFastRecentModif();
                    $prescription_sejour->countFastUrgence();
                }
            }
        }
        $GLOBALS["all_sejours"] = $all_sejours;
    }

    /**
     * Mettre en cache les lits
     *
     * @param CAffectation $affectation Affectation
     *
     * @return void
     * @throws Exception
     */
    private function cacheLit(CAffectation $affectation): void
    {
        // Cache des lits
        $lit_id = $affectation->lit_id;
        static $lits = [];
        if (!array_key_exists($lit_id, $lits)) {
            $lit = new CLit();
            $lit->load($lit_id);
            $lits[$lit_id] = $lit;
        }

        $lit                                       = $lits[$lit_id];
        $lit->_ref_affectations[$affectation->_id] = $affectation;

        // Cache des chambres
        $chambre_id = $lit->chambre_id;
        static $chambres = [];
        if (!array_key_exists($chambre_id, $chambres)) {
            $chambre = new CChambre();
            $chambre->load($chambre_id);
            $chambres[$chambre_id] = $chambre;
        }

        $chambre                     = $chambres[$chambre_id];
        $chambre->_ref_lits[$lit_id] = $lit;

        // Cache de services
        $sejoursParService = [];
        $service_id        = $chambre->service_id ?: $affectation->service_id;
        if (!array_key_exists($service_id, $sejoursParService)) {
            $service = new CService();
            $service->load($service_id);
            $sejoursParService[$service_id] = $service;
        }

        $service                             = $sejoursParService[$service_id];
        $service->_ref_chambres[$chambre_id] = $chambre;
        $GLOBALS["sejoursParService"]        = $sejoursParService;
    }

    /**
     * @param string|null  $default_services_id
     * @param bool|CGroups $group
     * @param mixed        $service_id
     * @param mixed        $praticien_id
     *
     * @return array
     * @throws Exception
     */
    private function getDefaultServiceId(
        ?string $default_services_id,
        CGroups $group,
        ?string $service_id,
        ?string $praticien_id
    ): array {
        $default_service_id  = "";
        $default_services_id = json_decode($default_services_id);
        if (isset($default_services_id->{"g$group->_id"})) {
            $default_service_id = explode("|", $default_services_id->{"g$group->_id"});
            $default_service_id = reset($default_service_id);
        }

        if (
            !$service_id &&
            $default_service_id &&
            !$praticien_id &&
            !CAppUI::conf("soins Sejour select_services_ids", $group)
        ) {
            $service_id = $default_service_id;
        }

        return [$default_service_id, $service_id];
    }
}
