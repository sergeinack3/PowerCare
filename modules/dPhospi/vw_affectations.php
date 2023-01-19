<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CPrestation;
use Ox\Mediboard\Hospi\CPrestationJournaliere;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

CAppUI::requireModuleFile("dPhospi", "inc_vw_affectations");

$date            = @CView::get("date", "date default|now", true);
$mode            = CView::get("mode", "bool default|0", true);
$services_ids    = CView::get("services_ids", "str", true);
$triAdm          = CView::get("triAdm", "enum list|date_entree|praticien|patient", true);
$_type_admission = CView::get("_type_admission", "enum list|ambucomp|ambucompssr|comp|ambu|exte|seances|ssr|psy|urg|consult default|ambucomp", true);
$filterFunction  = CView::get("filterFunction", "ref class|CFunctions", true);
$prestation_id   = CView::get("prestation_id", "str default|" . CAppUI::pref("prestation_id_hospi"), true);

CView::checkin();
$g = CGroups::loadCurrent()->_id;

$heureLimit = CAppUI::gconf("dPhospi General hour_limit");

// Si la date en session vient de la vue temporelle, on retransforme en date
$date = CMbDT::date($date);

if (is_array($services_ids)) {
  CMbArray::removeValue("", $services_ids);
}

if (!$services_ids) {
  $smarty = new CSmartyDP;
  $smarty->display("inc_no_services.tpl");
  CApp::rip();
}

$emptySejour                  = new CSejour();
$emptySejour->_type_admission = $_type_admission;

// Récupération du service à ajouter/éditer
$totalLits = 0;

// Récupération des chambres/services
$where              = array();
$where["group_id"]  = "= '$g'";
$where["cancelled"] = "= '0'";
$services           = new CService();
$order              = "externe, nom";
$services           = $services->loadListWithPerms(PERM_READ, $where, $order);

$count_services = 0;

$hotellerie_active = CModule::getActive("hotellerie");

// Chargement des services
foreach ($services as $service) {
  if (!in_array($service->_id, $services_ids)) {
    continue;
  }

  loadServiceComplet($service, $date, $mode, null, null, $prestation_id);
  loadAffectationsPermissions($service, $date, $mode, $prestation_id);
  $totalLits += $service->_nb_lits_dispo;

  if (count($service->_ref_chambres)) {
    $count_services++;

    if ($hotellerie_active) {
      foreach ($service->_ref_chambres as $_chambre) {
        foreach ($_chambre->_ref_lits as $_lit) {
          $cleanup = $_lit->loadLastCleanup($date);
          $cleanup->getColorStatusCleanup(count($_lit->_ref_affectations) > 0);
        }
      }
    }
  }
}

// Nombre de patients à placer pour la semaine qui vient (alerte)
$today   = CMbDT::date() . " 01:00:00";
$endWeek = CMbDT::dateTime("+7 days", $today);

$where                    = array();
$where["annule"]          = "= '0'";
$where["sejour.entree"]   = "BETWEEN '$today' AND '$endWeek'";
$where["sejour.group_id"] = "= '$g'";
if ($_type_admission == "ambucomp") {
  $where[] = "`sejour`.`type` = 'ambu' OR `sejour`.`type` = 'comp'";
}
elseif ($_type_admission == "ambucompssr") {
  $where[] = "`sejour`.`type` = 'ambu' OR `sejour`.`type` = 'comp' OR `sejour`.`type` = 'ssr'";
}
elseif ($_type_admission) {
  $where["sejour.type"] = " = '$_type_admission'";
}
else {
  $where["sejour.type"] = CSQLDataSource::prepareNotIn(CSejour::getTypesSejoursUrgence());
}
if ($_type_admission != "seances") {
  $where[] = "affectation.affectation_id IS NULL";
}

$where[]                 = "sejour.service_id " . CSQLDataSource::prepareIn($services_ids) . " OR sejour.service_id IS NULL";
$leftjoin["affectation"] = "sejour.sejour_id = affectation.sejour_id";

// Filtre sur les fonctions
if ($filterFunction) {
  $leftjoin["users_mediboard"]          = "sejour.praticien_id = users_mediboard.user_id";
  $where["users_mediboard.function_id"] = " = '$filterFunction'";
}

$sejour = new CSejour();
$alerte = $sejour->countList($where, null, $leftjoin);

// Liste des patients à placer
$groupSejourNonAffectes = array();

if (CCanDo::edit()) {
  $where                  = array();
  $where["sejour.annule"] = "= '0'";
  $where[]                = "sejour.service_id " . CSQLDataSource::prepareIn($services_ids) . " OR sejour.service_id IS NULL";

  switch ($_type_admission) {
    case "ambucomp":
      $where[] = "sejour.type = 'ambu' OR sejour.type = 'comp'";
      break;
    case "ambucompssr":
      $where[] = "sejour.type = 'ambu' OR sejour.type = 'comp' OR sejour.type = 'ssr'";
      break;

    case "0":
      break;

    default:
      $where["sejour.type"] = "= '$_type_admission'";
  }

  // Admissions de la veille
  $dayBefore                        = CMbDT::date("-1 days", $date);
  $where["sejour.entree"]           = "BETWEEN '$dayBefore 00:00:00' AND '$date 01:59:59'";
  $groupSejourNonAffectes["veille"] = loadSejourNonAffectes($where, null, null, $prestation_id);

  // Admissions du matin
  $where["sejour.entree"]          = "BETWEEN '$date 02:00:00' AND '$date " . CMbDT::time("-1 second", $heureLimit) . "'";
  $groupSejourNonAffectes["matin"] = loadSejourNonAffectes($where, null, null, $prestation_id);

  // Admissions du soir
  $where["sejour.entree"]         = "BETWEEN '$date $heureLimit' AND '$date 23:59:59'";
  $groupSejourNonAffectes["soir"] = loadSejourNonAffectes($where, null, null, $prestation_id);

  // Admissions antérieures
  $twoDaysBefore                   = CMbDT::date("-2 days", $date);
  $where["sejour.entree"]          = "<= '$twoDaysBefore 23:59:59'";
  $where["sejour.sortie"]          = ">= '$date 00:00:00'";
  $groupSejourNonAffectes["avant"] = loadSejourNonAffectes($where, null, null, $prestation_id);

  foreach ($groupSejourNonAffectes as $moment => &$_groupSejourNonAffectes) {
    switch ($triAdm) {
      case "date_entree":
          $order_groups = CMbArray::pluck($_groupSejourNonAffectes, "entree_prevue");
        array_multisort($order_groups, SORT_ASC, $_groupSejourNonAffectes);
        break;

      case "praticien":
          $order_function = CMbArray::pluck($_groupSejourNonAffectes, "_ref_praticien", "function_id");
          $order_user_last_name = CMbArray::pluck($_groupSejourNonAffectes, "_ref_praticien", "_user_last_name");
          $order_user_first_name = CMbArray::pluck($_groupSejourNonAffectes, "_ref_praticien", "_user_first_name");
        array_multisort(
            $order_function, SORT_ASC,
            $order_user_last_name, SORT_ASC,
            $order_user_first_name, SORT_ASC,
            $groupSejourNonAffectes[$moment]
        );
        break;

      case "patient":
          $order_nom = CMbArray::pluck($_groupSejourNonAffectes, "_ref_patient", "nom");
          $order_prenom = CMbArray::pluck($_groupSejourNonAffectes, "_ref_patient", "prenom");
        array_multisort($order_nom, SORT_ASC, $order_prenom, SORT_ASC, $groupSejourNonAffectes[$moment]);
        break;

      default:
    }

    foreach ($groupSejourNonAffectes[$moment] as $key => $_sejour) {
      unset($groupSejourNonAffectes[$moment][$key]);
      $groupSejourNonAffectes[$moment][$_sejour->_id] = $_sejour;
    }
  }

  // Affectations dans les couloirs
  $where                  = array();
  $where[]                = "affectation.service_id " . CSQLDataSource::prepareIn($services_ids);
  $where["sejour.annule"] = " = '0'";
  $where[]                = "(affectation.entree BETWEEN '$date 00:00:00' AND '$date 23:59:59')
            OR (affectation.sortie BETWEEN '$date 00:00:00' AND '$date 23:59:59')";

  switch ($_type_admission) {
    case "ambucomp":
      $where["sejour.type"] = "IN ('ambu', 'comp')";
      break;
    case 'ambucompssr':
      $where["sejour.type"] = "IN ('ambu', 'comp', 'ssr')";
      break;
    case "0":
      break;
    default:
      $where["sejour.type"] = "= '$_type_admission'";
  }

  $affectations = loadAffectationsCouloirs($where, null, null, $prestation_id);

  switch ($triAdm) {
    case "date_entree":
        $order_entree = CMbArray::pluck($affectations, "_ref_sejour", "entree_prevue");
      array_multisort($order_entree, SORT_ASC, $affectations);
      break;

    case "praticien":
        $order_function = CMbArray::pluck($affectations, "_ref_sejour", "_ref_praticien", "function_id");
        $order_last_name = CMbArray::pluck($affectations, "_ref_sejour", "_ref_praticien", "_user_last_name");
        $order_first_name = CMbArray::pluck($affectations, "_ref_sejour", "_ref_praticien", "_user_first_name");
      array_multisort(
        $order_function, SORT_ASC,
        $order_last_name, SORT_ASC,
        $order_first_name, SORT_ASC,
        $affectations
      );
      break;

    case "patient":
        $order_nom = CMbArray::pluck($affectations, "_ref_sejour", "_ref_patient", "nom");
        $order_prenom = CMbArray::pluck($affectations, "_ref_sejour", "_ref_patient", "prenom");
      array_multisort($order_nom, SORT_ASC, $order_prenom, SORT_ASC, $affectations);
      break;

    default:
  }

  foreach ($affectations as $_affectation) {
    $groupSejourNonAffectes["couloir"][$_affectation->service_id][] = $_affectation;
  }
}

$imeds_active = CModule::getActive("dPImeds");

$functions_filter = array();
foreach ($groupSejourNonAffectes as $_keyGroup => $_group) {
  if ($_keyGroup == "couloir") {
    continue;
  }
  if ($imeds_active) {
    CSejour::massLoadNDA($_group);
  }
  /** @var CSejour[] $_group */
  foreach ($_group as $_key => $_sejour) {
    $_sejour->loadRefChargePriceIndicator();
    $functions_filter[$_sejour->_ref_praticien->function_id] = $_sejour->_ref_praticien->_ref_function;
    if ($filterFunction && $filterFunction != $_sejour->_ref_praticien->function_id) {
      unset($groupSejourNonAffectes[$_keyGroup][$_key]);
    }

    $_sejour->loadRefPatient()->loadRefsPatientHandicaps();
  }
}

$affectation         = new CAffectation();
$affectation->entree = CMbDT::addDateTime("08:00:00", $date);
$affectation->sortie = CMbDT::addDateTime("23:00:00", $date);

// Chargement conf prestation
$systeme_presta = CAppUI::gconf("dPhospi prestations systeme_prestations");

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("services_ids", $services_ids);
$smarty->assign("services", $services);
$smarty->assign("affectation", $affectation);
$smarty->assign("pathos", new CDiscipline());
$smarty->assign("date", $date);
$smarty->assign("demain", CMbDT::date("+ 1 day", $date));
$smarty->assign("heureLimit", $heureLimit);
$smarty->assign("mode", $mode);
$smarty->assign("emptySejour", $emptySejour);
$smarty->assign("filterFunction", $filterFunction);
$smarty->assign("triAdm", $triAdm);
$smarty->assign("totalLits", $totalLits);
$smarty->assign("alerte", $alerte);
$smarty->assign("groupSejourNonAffectes", $groupSejourNonAffectes);
$smarty->assign("functions_filter", $functions_filter);
$smarty->assign("prestation_id", $prestation_id);
$smarty->assign("systeme_presta", $systeme_presta);
$smarty->assign("count_services", $count_services);

//chargement des prestations
if ($systeme_presta == "standard") {
  $prestations = CPrestation::loadCurrentList();
  $smarty->assign("prestations", $prestations);
}
else {
  $prestations_journalieres = CPrestationJournaliere::loadCurrentList();
  $smarty->assign("prestations_journalieres", $prestations_journalieres);
}

$smarty->display("vw_affectations.tpl");
