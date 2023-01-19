<?php

/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CPrestationJournaliere;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CSecondaryFunction;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$ds = CSQLDataSource::get("std");

$offline = CView::get("offline", 'bool default|0');

$now                     = CMbDT::date();
$filter                  = new COperation();
$filter->_datetime_min   = CView::get("_datetime_min", ['dateTime', 'default' => "$now 00:00:00"]);
$filter->_datetime_max   = CView::get("_datetime_max", ['dateTime', 'default' => "$now 23:59:59"]);
$filter->_prat_id        = CView::get("_prat_id", 'ref class|CMediusers');
$filter->_bloc_id        = CView::get("_bloc_id", 'str'); /* Can be an array of bloc ids */
$filter->_salle_id       = CView::get("_salle_id", 'str'); /* Can be an array  of salle ids */
$filter->_plage          = CView::get("_plage", 'bool default|' . CAppUI::gconf("dPbloc printing plage_vide"));
$filter->_ranking        = CView::get("_ranking", 'enum list|ok|ko');
$filter->_specialite     = CView::get("_specialite", 'ref class|CFunctions');
$filter->_codes_ccam     = CView::get("_codes_ccam", 'str');
$filter->exam_extempo    = CView::get("exam_extempo", 'bool');
$filter->_ccam_libelle   = CView::get("_ccam_libelle", 'str default|' . CAppUI::gconf("dPbloc printing libelle_ccam"));
$filter->_planning_perso = CView::get("planning_perso", 'str');
$filter->_nb_days        = CView::get("_nb_days", 'num default|0');
$filter->_by_prat        = CView::get("_by_prat", 'bool');
$filter->_by_bloc        = CView::get("_by_bloc", 'bool');

$_coordonnees            = CView::get("_coordonnees", 'bool');
$_print_numdoss          = CView::get("_print_numdoss", 'bool');
$_print_ipp              = CView::get("_print_ipp", 'bool');
$_print_annulees         = CView::get("_print_annulees", 'bool');
$_materiel               = CView::get("_materiel", 'bool default|' . CAppUI::gconf("dPbloc printing view_materiel"));
$_missing_materiel       = CView::get(
    "_missing_materiel",
    'bool default|' . CAppUI::gconf("dPbloc printing view_missing_materiel")
);
$_extra                  = CView::get("_extra", 'bool default|' . CAppUI::gconf("dPbloc printing view_extra"));
$_duree                  = CView::get("_duree", 'bool default|' . CAppUI::gconf("dPbloc printing view_duree"));
$_convalescence          = CView::get(
    '_convalescence',
    'bool default|' . CAppUI::gconf('dPbloc printing view_convalescence')
);
$_examens_perop          = CView::get('_examens_perop', 'bool default|0');
$_hors_plage             = CView::get("_hors_plage", 'bool');
$_show_comment_sejour    = CView::get("_show_comment_sejour", 'bool');
$_compact                = CView::get('_compact', 'bool default|0');
$_show_identity          = CView::get('_show_identity', 'bool default|1');
$_page_break             = CView::get('_page_break', 'bool default|0');
$_display_main_doctor    = CView::get('_display_main_doctor', 'bool default|0');
$_display_allergy        = CView::get('_display_allergy', 'bool default|0');
$no_consult_anesth       = CView::get("no_consult_anesth", 'bool');
$sejour_type             = CView::get("type", 'enum list|' . implode("|", CSejour::$types));
$_intervention_emergency = CView::get("_intervention_emergency", 'str');

CView::checkin();

if ($offline) {
    if (!$filter->_nb_days || $filter->_nb_days > 7) {
        $filter->_nb_days = 2;
    }
    CApp::setTimeLimit("600");
    CApp::setMemoryLimit("2048M");
    CView::enforceSlave();
}

if ($filter->_nb_days) {
    $filter->_datetime_max = CMbDT::date("+$filter->_nb_days days", CMbDT::date($filter->_datetime_min)) . " 21:00:00";
}

if (is_array($filter->_bloc_id)) {
    CMbArray::removeValue("0", $filter->_bloc_id);
}

$filterSejour       = new CSejour();
$filterSejour->type = $sejour_type;

$group = CGroups::loadCurrent();

// On sort les plages opératoires et les interventions hors plage
//  date - salle - horaires

$numOp = 0;

$affectations_plage = [];

$wherePlagesop   = [];
$whereOperations = [];

$wherePlagesop["plagesop.date"]           = $ds->prepare(
    "BETWEEN %1 AND %2",
    CMbDT::date($filter->_datetime_min),
    CMbDT::date($filter->_datetime_max)
);
$whereOperations["operations.date"]       = $ds->prepare(
    "BETWEEN %1 AND %2",
    CMbDT::date($filter->_datetime_min),
    CMbDT::date($filter->_datetime_max)
);
$whereOperations["operations.plageop_id"] = "IS NULL";

$user = CMediusers::get();

$praticien = new CMediusers();
$praticien->load($filter->_prat_id);

$prestation_id = CAppUI::pref("prestation_id_hospi");

if (CAppUI::gconf("dPhospi prestations systeme_prestations") == "standard" || $prestation_id == "all") {
    $prestation_id = "";
}

$prestation = new CPrestationJournaliere();
$prestation->load($prestation_id);

$anesth_id = null;
// dans le cas d'un anesthesiste, vider le prat_id si l'anesthesiste veut voir tous
// les plannings sinon laisser son prat_id pour afficher son planning perso
if ($praticien->isAnesth() && !$filter->_planning_perso) {
    $filter->_prat_id = null;
    $anesth_id        = $praticien->_id;
}

// Filtre sur les praticiens ou les spécialités
$function   = new CFunctions();
$functions  = [];
$praticiens = [];
// Aucun filtre de séléctionné : tous les éléments auxquels on a le droit
if (!$filter->_specialite && !$filter->_prat_id) {
    if (!$user->isAnesth() && !$praticien->isAnesth()) {
        $functions  = $function->loadListWithPerms(PERM_READ);
        $praticiens = $user->loadPraticiens();
    } else {
        $functions  = $function->loadList();
        $praticiens = $praticien->loadList();
    }
} elseif ($filter->_specialite) {
    // Filtre sur la specialité : la spec et ses chirs primaires et secondaires
    $function->load($filter->_specialite);
    $function->loadBackRefs("users");
    $function->loadBackRefs("secondary_functions");
    $functions[$function->_id] = $function;
    $praticiens                = $function->_back["users"];
    /** @var CSecondaryFunction $sec_func */
    foreach ($function->_back["secondary_functions"] as $sec_func) {
        if (!isset($praticiens[$sec_func->user_id])) {
            $sec_func->loadRefUser();
            $praticiens[$sec_func->user_id] = $sec_func->_ref_user;
        }
    }
} elseif ($filter->_prat_id) {
    // Filtre sur le chir : le chir et ses specs primaires et secondaires
    $praticien->loadRefFunction();
    $praticien->loadBackRefs("secondary_functions");
    $praticiens[$praticien->_id]        = $praticien;
    $functions[$praticien->function_id] = $praticien->_ref_function;
    /** @var CSecondaryFunction $sec_func */
    foreach ($praticien->_back["secondary_functions"] as $sec_func) {
        if (!isset($functions[$sec_func->function_id])) {
            $sec_func->loadRefFunction();
            $functions[$sec_func->function_id] = $sec_func->_ref_function;
        }
    }
}
$ljoin           = [];

// Liste des praticiens et fonctions à charger
if (!$anesth_id) {
  $in = CSQLDataSource::prepareIn(array_keys($praticiens));
  $subquery = "SELECT operations.operation_id FROM operations WHERE operations.plageop_id = plagesop.plageop_id
                 AND (operations.chir_2_id {$in} OR operations.chir_3_id {$in} OR operations.chir_4_id {$in})";
  $wherePlagesop[] = "plagesop.chir_id " . CSQLDataSource::prepareIn(array_keys($praticiens)) .
    " OR plagesop.spec_id " . CSQLDataSource::prepareIn(array_keys($functions)) .
    " OR plagesop.urgence = '1' OR EXISTS($subquery)";
  $whereOperations[] = "operations.chir_id {$in} OR operations.chir_2_id {$in} OR "
    . "operations.chir_3_id {$in} OR operations.chir_4_id {$in}";
}
else {
  $whereOperations[] = "operations.anesth_id = '$anesth_id' OR plagesop.anesth_id = '$anesth_id'";
  $ljoin["plagesop"] = "plagesop.plageop_id = operations.plageop_id";
}

// En fonction de la salle
$salle      = new CSalle();
$whereSalle = [];

$whereSalle["sallesbloc.bloc_id"] = CSQLDataSource::prepareIn(
    count($filter->_bloc_id) ?
        $filter->_bloc_id :
        array_keys($group->loadBlocs(PERM_READ))
);

if ($filter->_salle_id && !in_array(0, $filter->_salle_id)) {
    $whereSalle["sallesbloc.salle_id"] = CSQLDataSource::prepareIn($filter->_salle_id);
}
$listSalles = $salle->loadListWithPerms(PERM_READ, $whereSalle);
if (($filter->_salle_id && !in_array(0, $filter->_salle_id)) || $filter->_bloc_id) {
    $whereOperations["operations.salle_id"] = CSQLDataSource::prepareIn(array_keys($listSalles));
}

$whereOperations["sejour.group_id"] = "= '" . $group->_id . "'";

$wherePlagesop["plagesop.salle_id"] = CSQLDataSource::prepareIn(array_keys($listSalles));

$orderPlagesop = "date, sallesbloc.nom, debut";

$ljoinPlagesop = [
    "sallesbloc" => "plagesop.salle_id = sallesbloc.salle_id",
];
if ($filter->_by_bloc) {
    $orderPlagesop                    = "date, bloc_operatoire.nom, sallesbloc.nom, debut";
    $ljoinPlagesop["bloc_operatoire"] = "bloc_operatoire.bloc_operatoire_id = sallesbloc.bloc_id";
}
$plageop = new CPlageOp();
/** @var CPlageOp[] $plagesop */
$plagesop = $plageop->loadList($wherePlagesop, $orderPlagesop, null, null, $ljoinPlagesop);

CStoredObject::massLoadBackRefs($plagesop, "affectations_personnel");

$ljoin["sejour"] = "operations.sejour_id = sejour.sejour_id";
$where           = [];

if (!$anesth_id) {
    $in      = CSQLDataSource::prepareIn(array_keys($praticiens));
    $where[] = "operations.chir_id {$in} OR operations.chir_2_id {$in} 
    OR operations.chir_3_id {$in} OR operations.chir_4_id {$in}";
} else {
    $where[]           = "operations.anesth_id = '$anesth_id' OR plagesop.anesth_id = '$anesth_id'";
    $ljoin["plagesop"] = "plagesop.plageop_id = operations.plageop_id";
}

if (!$_print_annulees) {
    $where["operations.annulee"]           = "= '0'";
    $whereOperations["operations.annulee"] = "= '0'";
}

switch ($filter->_ranking) {
    case "ok":
        $where["operations.rank"] = "!= '0'";
        break;
    case "ko":
        $where["operations.rank"] = "= '0'";
        break;
    default:
}

if ($filter->_codes_ccam) {
    $where["operations.codes_ccam"]           = "LIKE '%$filter->_codes_ccam%'";
    $whereOperations["operations.codes_ccam"] = "LIKE '%$filter->_codes_ccam%'";
}
if ($filter->exam_extempo) {
    $where["operations.exam_extempo"]           = "= '1'";
    $whereOperations["operations.exam_extempo"] = "= '1'";
}
if ($filterSejour->type) {
    $where["sejour.type"]           = "= '$filterSejour->type'";
    $whereOperations["sejour.type"] = "= '$filterSejour->type'";
}
if ($_intervention_emergency != '') {
    $where["operations.urgence"]           = "= '$_intervention_emergency'";
    $whereOperations["operations.urgence"] = "= '$_intervention_emergency'";
}

if ($filter->_by_bloc) {
    $orderOperations          = "date, bloc_operatoire.nom, sallesbloc.nom, time_operation, chir_id";
    $ljoin["sallesbloc"]      = "operations.salle_id = sallesbloc.salle_id";
    $ljoin["bloc_operatoire"] = "bloc_operatoire.bloc_operatoire_id = sallesbloc.bloc_id";
} else {
    $orderOperations = "date, salle_id, time_operation, chir_id";
}

$operation = new COperation();
/** @var COperation[] $operations */
$operations = $operation->loadList($whereOperations, $orderOperations, null, null, $ljoin);
CStoredObject::massLoadFwdRef($operations, "plageop_id");
CStoredObject::massLoadFwdRef($operations, "type_anesth");
$sejours = CStoredObject::massLoadFwdRef($operations, "sejour_id");
CStoredObject::massLoadFwdRef($operations, "chir_id");
$patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
CStoredObject::massLoadBackRefs($operations, "affectations_personnel");
CStoredObject::massLoadBackRefs($operations, "dossiers_anesthesie");
CStoredObject::massLoadBackRefs($operations, "commande_op");

if ($prestation_id) {
    CSejour::massLoadLiaisonsForPrestation($sejours, $prestation_id);
}

if ($_print_numdoss) {
    CSejour::massLoadNDA($sejours);
}

if ($_print_ipp) {
    CPatient::massLoadIPP($patients);
}

if ($_display_allergy) {
    $dossiers_medicaux = CStoredObject::massLoadBackRefs($patients, "dossier_medical");
    foreach ($dossiers_medicaux as $_dossier) {
        $_dossier->loadRefsAllergies();
    }
}

$order = "operations.rank, operations.horaire_voulu, sejour.entree_prevue";

$listDates       = [];
$listDatesByPrat = [];
$listPrats       = [];

$format_print     = CAppUI::gconf("dPbloc printing format_print");
$systeme_materiel = CAppUI::gconf("dPbloc CPlageOp systeme_materiel");

$ordre_passage_temp = [];
$ordre_passage      = [];

// Operations de chaque plage
foreach ($plagesop as $plage) {
    $plage->loadRefsFwd(1);

    $where["operations.plageop_id"] = "= '$plage->_id'";

    $op = new COperation();
    /** @var COperation[] $listOp */
    $listOp = $op->loadList($where, $order, null, null, $ljoin);

    $chirs    = CStoredObject::massLoadFwdRef($listOp, "chir_id");
    $sejours  = CStoredObject::massLoadFwdRef($listOp, "sejour_id");
    $patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");

    CStoredObject::massLoadBackRefs($listOp, "affectations_personnel");
    CStoredObject::massLoadBackRefs($listOp, "dossiers_anesthesie");
    CStoredObject::massLoadBackRefs($listOp, "commande_op");
    CStoredObject::massLoadFwdRef($listOp, "type_anesth");

    if ($prestation_id) {
        CSejour::massLoadLiaisonsForPrestation($sejours, $prestation_id);
    }

    if ($_print_ipp) {
        CPatient::massLoadIPP($patients);
    }

    if ($_print_numdoss) {
        CSejour::massLoadNDA($sejours);
    }

    CStoredObject::massLoadBackRefs($patients, "bmr_bhre");

    foreach ($listOp as $key => $operation) {
        $operation->loadRefPlageOp();
        if (
            $operation->_datetime_best < $filter->_datetime_min || $operation->_datetime_best > $filter->_datetime_max
        ) {
            unset($listOp[$key]);
            continue;
        }
        $operation->loadRefsConsultAnesth();
        if ($no_consult_anesth && $operation->_ref_consult_anesth->_id) {
            unset($listOp[$operation->_id]);
        }
        $operation->loadRefPraticien();
        $operation->loadExtCodesCCAM();
        $operation->updateHeureUS();
        $operation->updateSalle();
        $operation->loadAffectationsPersonnel();
        $operation->loadRefCommande("bloc");
        $operation->computeStatusPanier();
        $operation->loadRefTypeAnesth();
        $sejour = $operation->loadRefSejour();
        $sejour->loadRefsFwd();
        $sejour->_ref_patient->updateBMRBHReStatus($sejour);

        if ($_display_main_doctor) {
            $sejour->_ref_patient->loadRefsCorrespondants();
        }

        $sejour->_ref_patient->getSurpoids();
        if ($_display_allergy) {
            $sejour->_ref_patient->loadRefDossierMedical();
        }

        if ($format_print == "advanced" || $format_print == 'advanced_2') {
            $operation->_liaisons_prestation                                                              = implode(
                "|",
                CMbArray::pluck($sejour->loadAllLiaisonsForDay(CMbDT::date($operation->_datetime_best)), "nom")
            );
            $ordre_passage_temp[$operation->chir_id][CMbDT::date($operation->_datetime)][$operation->_id] = $operation;
        }

        // Chargement de l'affectation
        $affectation = $operation->getAffectation();

        if ($affectation->_id) {
            $affectation->loadRefLit()->loadCompleteView();
        }
        $sejour->_ref_first_affectation = $affectation;

        // Chargement des ressources si gestion du materiel en mode expert
        if ($_materiel && $systeme_materiel == "expert") {
            $operation->loadRefsBesoins();
            foreach ($operation->_ref_besoins as $_besoin) {
                $_besoin->_available = $_besoin->isAvailable();
            }
        }
    }
    if ((count($listOp) == 0) && !$filter->_plage) {
        unset($plagesop[$plage->_id]);
        continue;
    }
    $plage->_ref_operations = $listOp;
    $numOp                  += count($listOp);

    // Chargement des affectation de la plage
    $plage->loadAffectationsPersonnel();

    // Initialisation des tableaux de stockage des affectation pour les op et les panseuses
    $affectations_plage[$plage->_id]["iade"]         = [];
    $affectations_plage[$plage->_id]["op"]           = [];
    $affectations_plage[$plage->_id]["op_panseuse"]  = [];
    $affectations_plage[$plage->_id]["sagefemme"]    = [];
    $affectations_plage[$plage->_id]["manipulateur"] = [];

    if (null !== $plage->_ref_affectations_personnel) {
        $affectations_plage[$plage->_id]["iade"]         = $plage->_ref_affectations_personnel["iade"];
        $affectations_plage[$plage->_id]["op"]           = $plage->_ref_affectations_personnel["op"];
        $affectations_plage[$plage->_id]["op_panseuse"]  = $plage->_ref_affectations_personnel["op_panseuse"];
        $affectations_plage[$plage->_id]["sagefemme"]    = $plage->_ref_affectations_personnel["sagefemme"];
        $affectations_plage[$plage->_id]["manipulateur"] = $plage->_ref_affectations_personnel["manipulateur"];
    }

    $order_salle                                        = $filter->_by_bloc ? $plage->_ref_salle->_view : 0;
    $listDates[$plage->date][$order_salle][$plage->_id] = $plage;

    if ($filter->_by_prat) {
        foreach ($plage->_ref_operations as $_op) {
            $listPrats[$_op->chir_id]                                = $_op->_ref_chir;
            $listDatesByPrat[$plage->date][$_op->chir_id][$_op->_id] = $_op;
        }
    }
}

foreach ($operations as $key => $operation) {
    $operation->loadRefPlageOp();

    if (
        $operation->_datetime_best < $filter->_datetime_min || $operation->_datetime_best > $filter->_datetime_max
    ) {
        unset($operations[$key]);
        continue;
    }
    $operation->loadRefsConsultAnesth();
    if ($no_consult_anesth && $operation->_ref_consult_anesth->_id) {
        unset($operations[$operation->_id]);
        continue;
    }
    $operation->loadRefPraticien();
    $operation->loadExtCodesCCAM();
    $operation->updateHeureUS();
    $operation->loadAffectationsPersonnel();
    $operation->loadRefCommande("bloc");
    $operation->computeStatusPanier();
    $operation->loadRefTypeAnesth();
    $sejour = $operation->loadRefSejour();
    $sejour->loadRefsFwd();
    $sejour->_ref_patient->updateBMRBHReStatus($sejour);
    if ($_display_allergy) {
        $sejour->_ref_patient->loadRefDossierMedical();
    }

    if ($_display_main_doctor) {
        $sejour->_ref_patient->loadRefsCorrespondants();
    }

    $sejour->_ref_patient->getSurpoids();

    if ($format_print == "advanced" || $format_print == 'advanced_2') {
        $operation->_liaisons_prestation                                                              = implode(
            "|",
            CMbArray::pluck($sejour->loadAllLiaisonsForDay(CMbDT::date($operation->_datetime_best)), "nom")
        );
        $ordre_passage_temp[$operation->chir_id][CMbDT::date($operation->_datetime)][$operation->_id] = $operation;
    }

    // Chargement de l'affectation
    $affectation = $operation->getAffectation();

    if ($affectation->_id) {
        $affectation->loadRefLit()->loadCompleteView();
    }
    $sejour->_ref_first_affectation = $affectation;

    // Chargement des ressources si gestion du materiel en mode expert
    if ($_materiel && CAppUI::gconf("dPbloc CPlageOp systeme_materiel") == "expert") {
        $operation->loadRefsBesoins();
        foreach ($operation->_ref_besoins as $_besoin) {
            $_besoin->loadRefTypeRessource();
        }
    }

    $salle       = $operation->_ref_salle;
    $order_salle = $filter->_by_bloc && $salle->_id ? $salle->_ref_bloc->nom . " - ZHors plage - " . $salle->nom : 0;

    $listDates[$operation->date][$order_salle]["hors_plage"][] = $operation;

    if ($filter->_by_prat) {
        $listPrats[$operation->chir_id]                                          = $operation->_ref_chir;
        $listDatesByPrat[$operation->date][$operation->chir_id][$operation->_id] = $operation;
    }
}

$numOp += count($operations);

ksort($listDates);

if ($filter->_by_bloc) {
    foreach ($listDates as $_date_op => $_ops_by_salle) {
        ksort($listDates[$_date_op]);
    }
}

if ($format_print == "advanced" || $format_print == 'advanced_2') {
    foreach ($ordre_passage_temp as $chir_id => $_ops_by_prat) {
        foreach ($_ops_by_prat as $_ops_by_date) {
            $ops_order = CMbArray::pluck($_ops_by_date, "_datetime");
            array_multisort($ops_order, SORT_ASC, $_ops_by_date);
            $i = 1;
            foreach ($_ops_by_date as $_op) {
                $ordre_passage[$_op->_id] = $i;
                $i++;
            }
        }
    }
}

if ($filter->_by_prat) {
    foreach ($listPrats as $_prat) {
        $_prat->loadRefFunction();
    }

    ksort($listDatesByPrat);
    foreach ($listDatesByPrat as &$_listDatesByPrat) {
        foreach ($_listDatesByPrat as &$listOps) {
            $list_order = CMbArray::pluck($listOps, "time_operation");
            array_multisort($list_order, SORT_ASC, $listOps);
        }
    }
}

$dossiers_soins = [];
$period         = CAppUI::gconf("soins offline_sejour period");

if ($offline) {
    foreach ($listDates as $_date => $listSalles) {
        foreach ($listSalles as $_ops_by_plage) {
            foreach ($_ops_by_plage as $key => $_ops) {
                if ($key != "hors_plage") {
                    $_ops = $_ops->_ref_operations;
                }
                foreach ($_ops as $_op) {
                    $params = [
                        "sejour_id"    => $_op->sejour_id,
                        "operation_id" => $_op->_id,
                        "dialog"       => 1,
                        "offline"      => 1,
                        "in_modal"     => 1,
                        "period"       => $period,
                    ];

                    $dossiers_soins[$_op->sejour_id] = CApp::fetch("soins", "print_dossier_soins", $params);
                }
            }
        }
    }
}

$colspan = intval($_materiel) + intval($_extra) + intval($_duree)
    + intval($_coordonnees) + intval($offline) + intval($_display_allergy) + 12;

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("affectations_plage", $affectations_plage);
$smarty->assign("filter", $filter);
$smarty->assign("prestation", $prestation);
$smarty->assign("_coordonnees", $_coordonnees);
$smarty->assign("_print_numdoss", $_print_numdoss);
$smarty->assign("_print_ipp", $_print_ipp);
$smarty->assign('_display_main_doctor', $_display_main_doctor);
$smarty->assign('_display_allergy', $_display_allergy);
$smarty->assign("listDates", $listDates);
$smarty->assign("operations", $operations);
$smarty->assign("numOp", $numOp);
$smarty->assign("_materiel", $_materiel);
$smarty->assign("_missing_materiel", $_missing_materiel);
$smarty->assign("_extra", $_extra);
$smarty->assign("_duree", $_duree);
$smarty->assign("_examens_perop", $_examens_perop);
$smarty->assign('_convalescence', $_convalescence);
$smarty->assign("_hors_plage", $_hors_plage);
$smarty->assign("_show_comment_sejour", $_show_comment_sejour);
$smarty->assign('_compact', $_compact);
$smarty->assign("_show_identity", $_show_identity);
$smarty->assign("ordre_passage", $ordre_passage);
$smarty->assign("_by_prat", $filter->_by_prat);
$smarty->assign("_by_bloc", $filter->_by_bloc);
$smarty->assign('_page_break', $_page_break);
$smarty->assign("offline", $offline);
$smarty->assign("dossiers_soins", $dossiers_soins);
$smarty->assign("colspan", $colspan);

if ($filter->_by_prat) {
    $smarty->assign("listDatesByPrat", $listDatesByPrat);
    $smarty->assign("listPrats", $listPrats);

    $smarty->display("view_planning_by_prat.tpl");
} else {
    switch ($format_print) {
        case "advanced":
            $smarty->display("view_planning_advanced");
            break;
        case 'advanced_2':
            $smarty->display("view_planning_advanced_2");
            break;
        default:
        case "standard":
            $smarty->display("view_planning");
            break;
    }
}
