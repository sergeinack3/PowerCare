<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Interop\Imeds\CImeds;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

$group = CGroups::loadCurrent();

$type            = CView::get("type", "str");
$type_mouvement  = CView::get("type_mouvement", "str");
$vue             = CView::get("vue", "bool default|0", true);
$praticien_id    = CView::get("praticien_id", "ref class|CMediusers", true);
$function_id     = CView::get("function_id", "ref class|CFunctions", true);
$services_ids    = CView::get("services_ids", "str", true);
$order_way       = CView::get("order_way", "enum list|ASC|DESC default|ASC", true);
$order_col       = CView::get(
  "order_col", "enum list|_chambre|_patient|_praticien|entree|sortie|_anesth|_hour|_heure_us default|" .
  (CAppUI::conf("dPhospi mouvements order_col_default_chambre", $group) ? "_chambre" : "_patient"), true
);
$mode            = CView::get("mode", "bool default|0", true);
$prestation_id   = CView::get("prestation_id", "str", true);
$hour_instantane = CView::get("hour_instantane", "num default|" . CMbDT::format(CMbDT::time(), "%H"), true);
$by_secteur      = CView::get("by_secteur", "bool default|0", true);
$type_hospi      = CView::get("type_hospi", "str", true);
$date            = CView::get("date", "date default|now", true);

CView::checkin();

$order_way = (CMbString::upper($order_way) === 'DESC') ? 'DESC' : 'ASC';

if (is_array($services_ids)) {
  CMbArray::removeValue("", $services_ids);
}

$show_duree_preop = CAppUI::conf("dPplanningOp COperation show_duree_preop");
$show_hour_anesth = CAppUI::gconf("dPhospi mouvements show_hour_anesth_mvt");

$services            = new CService();
$where               = array();
$where["service_id"] = CSQLDataSource::prepareIn($services_ids);
$services            = $services->loadList($where, "nom");

$secteurs = array();

if ($by_secteur) {
  CStoredObject::massLoadFwdRef($services, "secteur_id");

  foreach ($services as $_service) {
    $secteurs[$_service->secteur_id] = $_service->loadRefSecteur();
  }

  CMbArray::naturalSort($secteurs, array("nom"));
}

$update_count = "";

$praticien = new CMediusers();
$praticien->load($praticien_id);

$dmi_active = CModule::getActive("dmi");

$infos_interv = CAppUI::conf("dPhospi vue_temporelle infos_interv", $group);

if ($type == "presents") {
  $types_hospi = array("comp", "ambu", "ssr", "psy", "exte", "seances");
}
else {
  $types_hospi = array("comp", "ambu", "urg", "ssr", "psy", "exte", "seances");
}

$entrees = array();
$sorties = array();

$patients_desectorises = array();

// Récupération de la liste des services
$where             = array();
$where["externe"]  = "= '0'";
$where["sejour.group_id"] = "= '$group->_id'";

// Récupération de la journée à afficher
$limit1 = $date . " 00:00:00";
$limit2 = $date . " 23:59:59";

// Patients placés
$affectation                     = new CAffectation();
$ljoin                           = array();
$ljoin["sejour"]                 = "sejour.sejour_id = affectation.sejour_id";
$ljoin["patients"]               = "sejour.patient_id = patients.patient_id";
$ljoin["users"]                  = "sejour.praticien_id = users.user_id";
$ljoin["service"]                = "service.service_id = affectation.service_id";
$where                           = array();
$where["service.group_id"]       = "= '$group->_id'";
$where["affectation.service_id"] = CSQLDataSource::prepareIn($services_ids);
$where["affectation.lit_id"]     = "IS NOT NULL";
$where["sejour.type"]            = CSQLDataSource::prepareIn($types_hospi, $type_hospi);
if ($praticien_id) {
  $where["sejour.praticien_id"] = "= '$praticien->_id'";
}
if ($function_id) {
  $ljoin["users_mediboard"]             = "users_mediboard.user_id = users.user_id";
  $where["users_mediboard.function_id"] = "= '$function_id'";
}

// Patients non placés
$sejour                                = new CSejour();
$ljoinNP                               = array();
$ljoinNP["affectation"]                = "sejour.sejour_id    = affectation.sejour_id";
$ljoinNP["patients"]                   = "sejour.patient_id   = patients.patient_id";
$ljoinNP["users"]                      = "sejour.praticien_id = users.user_id";
$whereNP                               = array();
$whereNP["sejour.group_id"]            = "= '$group->_id'";
$whereNP["sejour.type"]                = CSQLDataSource::prepareIn($types_hospi, $type_hospi);
$whereNP["affectation.affectation_id"] = "IS NULL";
$whereNP["affectation.lit_id"]         = "IS NULL";
$whereNP["sejour.annule"]              = "= '0'";

if (count($services_ids)) {
  // Tenir compte des affectations sans lit_id (dans le couloir du service)
  unset($whereNP["affectation.affectation_id"]);
  unset($whereNP["affectation.lit_id"]);
  $whereNP[] = "((sejour.service_id " . CSQLDataSource::prepareIn($services_ids) .
    " OR sejour.service_id IS NULL) AND affectation.affectation_id IS NULL) OR " .
    "(affectation.lit_id IS NULL AND affectation.service_id " . CSQLDataSource::prepareIn($services_ids) .
    " AND affectation.entree <= '$date 23:59:59')";
}
if ($praticien->_id) {
  $whereNP["sejour.praticien_id"] = "= '$praticien->_id'";
}
if ($function_id) {
  $ljoinNP["users_mediboard"]             = "users_mediboard.user_id = sejour.praticien_id";
  $whereNP["users_mediboard.function_id"] = "= '$function_id'";
}
$order = $orderNP = null;

if ($order_col == "_patient") {
  $order = $orderNP = "patients.nom $order_way, patients.prenom, sejour.entree";
}

if ($order_col == "_praticien") {
  $order = $orderNP = "users.user_last_name $order_way, users.user_first_name";
}

if ($order_col == "_chambre") {
  $ljoin["lit"]     = "lit.lit_id = affectation.lit_id";
  $ljoin["chambre"] = "chambre.chambre_id = lit.chambre_id";
  $order            = "ISNULL(chambre.rank), chambre.rank $order_way, chambre.nom $order_way, ISNULL(lit.rank), lit.rank $order_way, lit.nom $order_way, patients.nom, patients.prenom, sejour.entree";
}

if ($order_col == "sortie") {
  $order   = "affectation.sortie $order_way, patients.nom, patients.prenom";
  $orderNP = "sejour.sortie $order_way, patients.nom, patients.prenom";
}

if ($order_col == "entree") {
  $order   = "affectation.entree $order_way, patients.nom, patients.prenom";
  $orderNP = "sejour.entree $order_way, patients.nom, patients.prenom";
}

// Récupération des présents du jour
if ($type == "presents") {
  $datetime_check = "$date $hour_instantane:00:00";

  // Patients placés
  if ($mode) {
    $where[] = "'$date' BETWEEN DATE(affectation.entree) AND DATE(affectation.sortie)";
  }
  else {
    $where[] = "('$datetime_check' BETWEEN affectation.entree AND affectation.sortie)" . ($date == CMbDT::date() ? "AND affectation.effectue = '0'" : "");
  }
  if ($vue) {
    $where["sejour.confirme"] = " IS NULL";
  }
  /** @var CAffectation[] $presents */
  $presents = $affectation->loadList($where, $order, null, "affectation_id", $ljoin, null, null, false);

  CAffectation::massUpdateView($presents);

  // Patients non placés
  if ($mode) {
    $whereNP[] = "'$date' BETWEEN DATE(sejour.entree) AND DATE(sejour.sortie)";
    $whereNP[] = "(affectation.affectation_id IS NULL) OR ('$date' BETWEEN DATE(affectation.entree) AND DATE(affectation.sortie))";
  }
  else {
    $whereNP[] = "'$datetime_check' BETWEEN sejour.entree AND sejour.sortie";
    $whereNP[] = "(affectation.affectation_id IS NULL) OR ('$datetime_check' BETWEEN affectation.entree AND affectation.sortie)";
  }

  /** @var CSejour[] $presentsNP */
  $presentsNP = $sejour->loadList($whereNP, $orderNP, null, "sejour.sejour_id", $ljoinNP);

  $update_count = count($presents) . "/" . count($presentsNP);

  /** @var CSejour[] $sejours */
  $sejours = CStoredObject::massLoadFwdRef($presents, "sejour_id");
  CSejour::massLoadNDA($sejours);
  CStoredObject::massLoadFwdRef($sejours, "patient_id");
  CStoredObject::massLoadFwdRef($sejours, "praticien_id");

  // Chargements des détails des séjours
  foreach ($presents as $key => $_present) {
    $_present->loadRefSejour();
    $_present->loadRefsAffectations();
    $_present->_ref_prev->updateView();
    $_present->_ref_next->updateView();
    $sejour = $_present->_ref_sejour;
    $sejour->loadRefPatient(1);
    $sejour->loadRefPraticien(1);
    $sejour->checkDaysRelative($date);
    $sejour->loadRefsOperations(['annulee' => "= '0'"]);

    if ($infos_interv || $show_hour_anesth) {
      foreach ($sejour->_ref_operations as $_op) {
        $_op->loadRefAnesth()->loadRefFunction();
        if ($_op->date >= $date) {
          $_op->loadRefPlageOp();
          $sejour->_ref_next_operation = $_op;
          break;
        }
      }
    }

    if ($show_duree_preop || $show_hour_anesth) {
      $op = $sejour->loadRefCurrOperation($date);
      $op->loadRefPlageOp();
      if ($show_duree_preop) {
        $op->updateHeureUS();
      }
      if ($show_hour_anesth) {
        $op->_ref_anesth->loadRefFunction();
      }
    }

    if ($dmi_active) {
      foreach ($sejour->_ref_operations as $_interv) {
        $_interv->getDMIAlert();
      }
    }

    $_present->_ref_next->loadRefLit(1)->loadCompleteView();
  }

  CSejour::massLoadLiaisonsForPrestation($sejours, $prestation_id, $date);

  /*if ($order_col == "_chambre") {
    //$order = "chambre.nom $order_way, patients.nom, patients.prenom";
    $sorter_lit       = CMbArray::pluck($presents, "_view");
    $sorter_patient   = CMbArray::pluck($presents, "_ref_sejour", "_ref_patient", "_view");

    array_multisort(
      $sorter_lit, constant("SORT_$order_way"),
      $sorter_patient, SORT_ASC,
      $presents
    );
  }*/

  otherOrder($presents, "", $order_col, $order_way);

  CStoredObject::massLoadFwdRef($presentsNP, "patient_id");
  CStoredObject::massLoadFwdRef($presentsNP, "praticien_id");

  foreach ($presentsNP as $sejour) {
    $sejour->loadRefPatient(1);
    $sejour->loadRefPraticien(1);
    $sejour->checkDaysRelative($date);
    $sejour->loadRefsOperations(['annulee' => "= '0'"]);

    if ($show_duree_preop || $show_hour_anesth) {
      foreach ($sejour->_ref_operations as $_op) {
        $_op->loadRefAnesth()->loadRefFunction();
      }

      $op = $sejour->loadRefCurrOperation($date);
      if ($show_duree_preop) {
        $op->updateHeureUS();
      }
      if ($show_hour_anesth) {
        $op->loadRefPlageOp();
        $op->_ref_anesth->loadRefFunction();
      }
    }

    if ($dmi_active) {
      foreach ($sejour->_ref_operations as $_interv) {
        $_interv->getDMIAlert();
      }
    }
  }

  CSejour::massLoadLiaisonsForPrestation($presentsNP, $prestation_id, $date);

  otherOrder($presentsNP, "np", $order_col, $order_way);

  $presents_by_service   = array();
  $presentsNP_by_service = array();

  foreach ($presents as $_present) {
    $key = getKeyServiceSecteur($_present->service_id, $by_secteur, $services);
    if (!isset($presents_by_service[$key])) {
      $presents_by_service[$key] = array();
    }
    $presents_by_service[$key][] = $_present;
  }
  uksort($presents_by_service, cmp(array_keys($by_secteur ? $secteurs : $services)));

  $presents = $presents_by_service;

  foreach ($presentsNP as $_present) {
    $key = getKeyServiceSecteur($_present->service_id, $by_secteur, $services);
    if (!isset($presentsNP_by_service[$key])) {
      $presentsNP_by_service[$key] = array();
    }
    $presentsNP_by_service[$key][] = $_present;
  }
  uksort($presentsNP_by_service, cmp(array_keys($by_secteur ? $secteurs : $services)));

  $presentsNP = $presentsNP_by_service;

  // Patients désectorisés
  foreach ($presents as $_presents_by_service) {
    foreach ($_presents_by_service as $_present) {
      $tmp_sejour = $_present->_ref_sejour;

      $tmp_sejour->service_id = null;
      $tmp_sejour->getServiceFromSectorisationRules(true);

      if ($_present->service_id != $tmp_sejour->service_id) {
        $tmp_sejour->loadRefService();
        $services[$tmp_sejour->_ref_service->_id] = $tmp_sejour->_ref_service;
        $patients_desectorises[]                  = $_present;
      }
    }
  }
}
// Récupération des déplacements du jour
elseif ($type == "mouvements") {
  if ($vue && $date == CMbDT::date()) {
    $where["effectue"] = "= '0'";
  }

  $whereEntrants                       = $whereSortants = $where;
  $whereSortants["affectation.sortie"] = "BETWEEN '$limit1' AND '$limit2'";
  $whereEntrants["affectation.entree"] = "BETWEEN '$limit1' AND '$limit2'";
  $whereEntrants["sejour.entree"]      = "!= affectation.entree";
  $whereSortants["sejour.sortie"]      = "!= affectation.sortie";

  unset($whereEntrants["service.group_id"]);
  unset($whereSortants["service.group_id"]);

  $whereEntrants["sejour.group_id"] = "= '$group->_id'";
  $whereSortants["sejour.group_id"] = "= '$group->_id'";

  /** @var CAffectation[] $deplacements */
  /** @var CAffectation[] $dep_entrants */
  /** @var CAffectation[] $dep_sortants */
  $dep_entrants = $affectation->loadList($whereEntrants, $order, null, "affectation_id", $ljoin, null, null, false);
  $dep_sortants = $affectation->loadList($whereSortants, $order, null, "affectation_id", $ljoin, null, null, false);
  $deplacements = array_merge($dep_entrants, $dep_sortants);
  $sejours      = CStoredObject::massLoadFwdRef($deplacements, "sejour_id");
  $patients     = CStoredObject::massLoadFwdRef($sejours, "patient_id");
  $praticiens   = CStoredObject::massLoadFwdRef($sejours, "praticien_id");
  CStoredObject::massLoadFwdRef($praticiens, "function_id");
  CStoredObject::massLoadFwdRef($deplacements, "lit_id");
  CSejour::massLoadNDA($sejours);
  CAffectation::massUpdateView($deplacements);

  $update_count = count($dep_entrants) . "/" . count($dep_sortants);

  foreach ($deplacements as $_deplacement) {
    $_deplacement->loadRefsFwd();
    $sejour = $_deplacement->_ref_sejour;
    $sejour->loadRefPatient(1);
    $sejour->loadRefPraticien(1);
    $_deplacement->_ref_next->loadRefLit()->loadCompleteView();
    $_deplacement->_ref_prev->loadRefLit()->loadCompleteView();

    if ($infos_interv || $show_hour_anesth) {
      $sejour->loadRefsOperations(['annulee' => "= '0'"]);
      foreach ($sejour->_ref_operations as $_op) {
        $_op->loadRefAnesth()->loadRefFunction();
        if ($_op->date >= $date) {
          $_op->loadRefPlageOp();
          $sejour->_ref_next_operation = $_op;
          break;
        }
      }
    }

    if ($show_hour_anesth) {
      $op = $sejour->loadRefCurrOperation($date);
      if (!$op->_id) {
          $op = $sejour->loadRefLastOperation(true);
      }
      $op->loadRefPlageOp();
      $op->_ref_anesth->loadRefFunction();
    }
  }

  CSejour::massLoadLiaisonsForPrestation($sejours, $prestation_id, $date);

  $dep_entrants_by_service = array();
  $dep_sortants_by_service = array();

  otherOrder($dep_entrants, "", $order_col, $order_way);

  foreach ($dep_entrants as $_dep_entrant) {
    $key = getKeyServiceSecteur($_dep_entrant->service_id, $by_secteur, $services);
    if (!isset($dep_entrants_by_service[$key])) {
      $dep_entrants_by_service[$key] = array();
    }
    $dep_entrants_by_service[$key][] = $_dep_entrant;
  }
  uksort($dep_entrants_by_service, cmp(array_keys($by_secteur ? $secteurs : $services)));

  $dep_entrants = $dep_entrants_by_service;

  otherOrder($dep_sortants, "", $order_col, $order_way);

  foreach ($dep_sortants as $_dep_sortant) {
    $key = getKeyServiceSecteur($_dep_sortant->service_id, $by_secteur, $services);
    if (!isset($dep_sortants_by_service[$key])) {
      $dep_sortants_by_service[$key] = array();
    }
    $dep_sortants_by_service[$key][] = $_dep_sortant;
  }

  uksort($dep_sortants_by_service, cmp(array_keys($by_secteur ? $secteurs : $services)));

  $dep_sortants = $dep_sortants_by_service;
}
// Récupération des entrées du jour
elseif ($type_mouvement == "entrees") {
  // Patients placés
  $where["affectation.entree"] = "BETWEEN '$limit1' AND '$limit2'";
  $where["sejour.entree"]      = "= affectation.entree";
  $where["sejour.type"]        = " = '$type'";

  /** @var CAffectation[] $mouvements */
  $mouvements = $affectation->loadList($where, $order, null, null, $ljoin, null, null, false);

  CAffectation::massUpdateView($mouvements);
  /** @var CSejour[] $sejours */
  $sejours = CStoredObject::massLoadFwdRef($mouvements, "sejour_id");
  CSejour::massLoadNDA($sejours);
  CStoredObject::massLoadFwdRef($sejours, "patient_id");
  CStoredObject::massLoadFwdRef($sejours, "praticien_id");

  // Chargements des détails des séjours
  foreach ($mouvements as $_mouvement) {
    $_mouvement->loadRefSejour();
    $_mouvement->loadRefsAffectations();
    $_mouvement->_ref_prev->updateView();
    $_mouvement->_ref_next->updateView();
    $sejour = $_mouvement->_ref_sejour;
    $sejour->loadRefPatient(1);
    $sejour->loadRefPraticien(1);
    $sejour->checkDaysRelative($date);
    $sejour->loadRefsOperations(["annulee" => "= '0'"]);

    if ($infos_interv || $show_hour_anesth) {
      foreach ($sejour->_ref_operations as $_op) {
        $_op->loadRefAnesth()->loadRefFunction();
        if ($_op->date >= $date) {
          $_op->loadRefPlageOp();
          $sejour->_ref_next_operation = $_op;
          break;
        }
      }
    }

    if ($show_duree_preop || $show_hour_anesth) {
      $op = $sejour->loadRefCurrOperation($date);
      $op->loadRefPlageOp();
      if ($show_duree_preop) {
        $op->updateHeureUS();
      }
      if ($show_hour_anesth) {
        $op->_ref_anesth->loadRefFunction();
      }
    }

    if ($dmi_active) {
      foreach ($sejour->_ref_operations as $_interv) {
        $_interv->getDMIAlert();
      }
    }

    $_mouvement->_ref_next->loadRefLit(1)->loadCompleteView();
  }

  CSejour::massLoadLiaisonsForPrestation($sejours, $prestation_id, $date);

  otherOrder($mouvements, "", $order_col, $order_way);

  // Patients non placés
  $whereNP["sejour.entree"] = "BETWEEN '$limit1' AND '$limit2'";
  $whereNP["sejour.type"]   = " = '$type'";
  /** @var CSejour[] $mouvementsNP */
  $mouvementsNP = $sejour->loadList($whereNP, $orderNP, null, "sejour.sejour_id", $ljoinNP);

  CStoredObject::massLoadFwdRef($mouvementsNP, "patient_id");
  CStoredObject::massLoadFwdRef($mouvementsNP, "praticien_id");
  CSejour::massLoadNDA($mouvementsNP);

  // Chargements des détails des séjours
  foreach ($mouvementsNP as $sejour) {
    $sejour->loadRefPatient(1);
    $sejour->loadRefPraticien(1);
    $sejour->checkDaysRelative($date);
    $sejour->loadRefsOperations(['annulee' => "= '0'"]);

    if ($show_duree_preop || $show_hour_anesth) {
      foreach ($sejour->_ref_operations as $_op) {
        $_op->loadRefAnesth()->loadRefFunction();
      }

      $op = $sejour->loadRefCurrOperation($date);
      if ($show_duree_preop) {
        $op->updateHeureUS();
      }
      if ($show_hour_anesth) {
        $op->loadRefPlageOp();
        $op->_ref_anesth->loadRefFunction();
      }
    }
    if ($dmi_active) {
      foreach ($sejour->_ref_operations as $_interv) {
        $_interv->getDMIAlert();
      }
    }
  }

  CSejour::massLoadLiaisonsForPrestation($mouvementsNP, $prestation_id, $date);

  $update_count = count($mouvements) . "/" . count($mouvementsNP);

  /** @var CAffectation[] $mouvements_by_service */
  /** @var CAffectation[] $mouvementsNP_by_service */
  $mouvements_by_service   = array();
  $mouvementsNP_by_service = array();

  foreach ($mouvements as $_mouvement) {
    $key = getKeyServiceSecteur($_mouvement->service_id, $by_secteur, $services);
    if (!isset($mouvements_by_service[$key])) {
      $mouvements_by_service[$key] = array();
    }
    $mouvements_by_service[$key][] = $_mouvement;
  }
  uksort($mouvements_by_service, cmp(array_keys($by_secteur ? $secteurs : $services)));

  $mouvements = $mouvements_by_service;

  CSejour::massLoadCurrAffectation($mouvementsNP, $date . " " . CMbDT::time());

  otherOrder($mouvementsNP, "np", $order_col, $order_way);

  foreach ($mouvementsNP as $_mouvement) {
    $_service_id = $_mouvement->service_id;
    if ($_mouvement->_ref_curr_affectation->service_id && !$_mouvement->_ref_curr_affectation->lit_id) {
      $_service_id = $_mouvement->_ref_curr_affectation->service_id;
    }
    $key = getKeyServiceSecteur($_service_id, $by_secteur, $services);
    if (!isset($mouvementsNP_by_service[$key])) {
      $mouvementsNP_by_service[$key] = array();
    }
    $mouvementsNP_by_service[$key][] = $_mouvement;
  }
  uksort($mouvementsNP_by_service, cmp(array_keys($by_secteur ? $secteurs : $services)));

  $mouvementsNP = $mouvementsNP_by_service;
}
// Récupération des sorties du jour
else {
  // Patients placés
  $where["affectation.sortie"] = "BETWEEN '$limit1' AND '$limit2'";
  $where["sejour.sortie"]      = "= affectation.sortie";
  $where["sejour.type"]        = " = '$type'";
  if ($vue) {
    $where["sejour.confirme"] = " IS NULL";
  }

  /** @var CAffectation[] $mouvements */
  $mouvements = $affectation->loadList($where, $order, null, null, $ljoin, null, null, false);
  CAffectation::massUpdateView($mouvements);
  $sejours    = CStoredObject::massLoadFwdRef($mouvements, "sejour_id");
  CStoredObject::massLoadFwdRef($sejours, "patient_id");
  CStoredObject::massLoadFwdRef($sejours, "praticien_id");
  CSejour::massLoadNDA($sejours);

  // Chargements des détails des séjours
  foreach ($mouvements as $_mouvement) {
    $_mouvement->loadRefSejour();
    $_mouvement->loadRefsAffectations();
    $_mouvement->_ref_prev->updateView();
    $_mouvement->_ref_next->updateView();
    $sejour = $_mouvement->_ref_sejour;
    $sejour->loadRefPatient(1);
    $sejour->loadRefPraticien(1);
    $sejour->checkDaysRelative($date);
    $sejour->loadRefsOperations(['annulee' => "= '0'"]);

    if ($show_duree_preop || $show_hour_anesth) {
      foreach ($sejour->_ref_operations as $_op) {
        $_op->loadRefAnesth()->loadRefFunction();
      }

      $op = $sejour->loadRefCurrOperation($date);
      if ($show_duree_preop) {
        $op->updateHeureUS();
      }
      if ($show_hour_anesth) {
        $op->loadRefPlageOp();
        $op->_ref_anesth->loadRefFunction();
      }
    }

    if ($dmi_active) {
      foreach ($sejour->_ref_operations as $_interv) {
        $_interv->getDMIAlert();
      }
    }

    $_mouvement->_ref_next->loadRefLit(1)->loadCompleteView();
  }

  CSejour::massLoadLiaisonsForPrestation($sejours, $prestation_id, $date);

  otherOrder($mouvements, "", $order_col, $order_way);

  // Patients non placés
  $whereNP["sejour.sortie"] = "BETWEEN '$limit1' AND '$limit2'";
  $whereNP["sejour.type"]   = " = '$type'";
  /** @var CSejour[] $mouvementsNP */
  $mouvementsNP = $sejour->loadList($whereNP, $orderNP, null, null, $ljoinNP);
  CStoredObject::massLoadFwdRef($mouvementsNP, "patient_id");
  CStoredObject::massLoadFwdRef($mouvementsNP, "praticien_id");
  CSejour::massLoadNDA($mouvementsNP);

  // Chargements des détails des séjours
  foreach ($mouvementsNP as $sejour) {
    $sejour->loadRefPatient(1);
    $sejour->loadRefPraticien(1);
    $sejour->checkDaysRelative($date);
    $sejour->loadRefsOperations(['annulee' => "= '0'"]);

    if ($show_duree_preop || $show_hour_anesth) {
      foreach ($sejour->_ref_operations as $_op) {
        $_op->loadRefAnesth()->loadRefFunction();
      }

      $op = $sejour->loadRefCurrOperation($date);
      if ($show_duree_preop) {
        $op->updateHeureUS();
      }
      if ($show_hour_anesth) {
        $op->loadRefPlageOp();
        $op->_ref_anesth->loadRefFunction();
      }
    }

    if ($dmi_active) {
      foreach ($sejour->_ref_operations as $_interv) {
        $_interv->getDMIAlert();
      }
    }
  }

  CSejour::massLoadLiaisonsForPrestation($mouvementsNP, $prestation_id, $date);

  otherOrder($mouvementsNP, "np", $order_col, $order_way);

  $update_count = count($mouvements) . "/" . count($mouvementsNP);

  $mouvements_by_service   = array();
  $mouvementsNP_by_service = array();

  $update_count = count($mouvements) . "/" . count($mouvementsNP);

  foreach ($mouvements as $_mouvement) {
    $key = getKeyServiceSecteur($_mouvement->service_id, $by_secteur, $services);
    if (!isset($mouvements_by_service[$key])) {
      $mouvements_by_service[$key] = array();
    }
    $mouvements_by_service[$key][] = $_mouvement;
  }
  uksort($mouvements_by_service, cmp(array_keys($by_secteur ? $secteurs : $services)));

  $mouvements = $mouvements_by_service;

  CSejour::massLoadCurrAffectation($mouvementsNP, "$date 00:00:00");
  foreach ($mouvementsNP as $_mouvement) {
    $_service_id = $_mouvement->service_id;
    if ($_mouvement->_ref_curr_affectation->service_id && !$_mouvement->_ref_curr_affectation->lit_id) {
      $_service_id = $_mouvement->_ref_curr_affectation->service_id;
    }
    $key = getKeyServiceSecteur($_service_id, $by_secteur, $services);
    if (!isset($mouvementsNP_by_service[$key])) {
      $mouvementsNP_by_service[$key] = array();
    }
    $mouvementsNP_by_service[$key][] = $_mouvement;
  }
  uksort($mouvementsNP_by_service, cmp(array_keys($by_secteur ? $secteurs : $services)));

  $mouvementsNP = $mouvementsNP_by_service;
}

function otherOrder(&$mouvements, $type, $order_col, $order_way) {
    if (!$mouvements) {
        $mouvements = [];
    }

  if (!in_array($order_col, array("_anesth", "_hour", "_heure_us"))) {
    return;
  }
  $sorter_patient = $type == "np" ?
    @CMbArray::pluck($mouvements, "_ref_patient", "_view") :
    @CMbArray::pluck($mouvements, "_ref_sejour", "_ref_patient", "_view");

  switch ($order_col) {
    default:
    case "_hour":
      $sorter_other = $type == "np" ?
        @CMbArray::pluck($mouvements, "_ref_curr_operation", "_datetime_best") :
        @CMbArray::pluck($mouvements, "_ref_sejour", "_ref_curr_operation", "_datetime_best");
      break;
    case "_anesth":
      $sorter_other = $type == "np" ?
        @CMbArray::pluck($mouvements, "_ref_curr_operation", "_ref_anesth", "_view") :
        @CMbArray::pluck($mouvements, "_ref_sejour", "_ref_curr_operation", "_ref_anesth", "_view");
      break;
    case "_heure_us":
      $sorter_other = $type == "np" ?
        @CMbArray::pluck($mouvements, "_ref_curr_operation", "_heure_us") :
        @CMbArray::pluck($mouvements, "_ref_sejour", "_ref_curr_operation", "_heure_us");
  }

  @array_multisort(
    $sorter_other, constant("SORT_$order_way"),
    $sorter_patient, SORT_ASC,
    $mouvements
  );
}

function getKeyServiceSecteur($service_id, $by_secteur, $services) {
  if (!$service_id || !$by_secteur) {
    return $service_id;
  }

  return $services[$service_id]->secteur_id;
}

function cmp($array) {
  return function ($a, $b) use (&$array) {
    return (array_search($a, $array) > array_search($b, $array));
  };
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("praticien", $praticien);
$smarty->assign("type", $type);
$smarty->assign("type_mouvement", $type_mouvement);
$smarty->assign("type_hospi", $type_hospi);
$smarty->assign("order_way", $order_way);
$smarty->assign("order_col", $order_col);
$smarty->assign("date", $date);
$smarty->assign("services", $services);
$smarty->assign("secteurs", $secteurs);
$smarty->assign("by_secteur", $by_secteur);

if ($type == "mouvements") {
  $smarty->assign("dep_entrants", $dep_entrants);
  $smarty->assign("dep_sortants", $dep_sortants);
  $smarty->assign("update_count", $update_count);
}
elseif ($type == "presents") {
  $smarty->assign("mouvements", $presents);
  $smarty->assign("mouvementsNP", $presentsNP);
  $smarty->assign("update_count", $update_count);
  $smarty->assign("mode", $mode);
  $smarty->assign("hour_instantane", $hour_instantane);
  $smarty->assign("patients_desectorises", $patients_desectorises);
}
else {
  $smarty->assign("mouvements", $mouvements);
  $smarty->assign("mouvementsNP", $mouvementsNP);
  $smarty->assign("update_count", $update_count);
}
$smarty->assign("vue", $vue);
$smarty->assign("canPlanningOp", CModule::getCanDo("dPplanningOp"));
$smarty->assign("isImedsInstalled", (CModule::getActive("dPImeds") && CImeds::getTagCIDC(CGroups::loadCurrent())));

$smarty->display("inc_list_sorties.tpl");
