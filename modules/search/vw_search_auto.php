<?php
/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Atih\CRSS;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Medicament\CMedicamentClasseATC;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Search\CSearch;
use Ox\Mediboard\Search\CSearchTargetEntry;
use Ox\Mediboard\Search\CSearchThesaurusEntry;

CCanDo::checkRead();

// Param
$prescription_id = CView::get("prescription_id", "num");
$sejour_id       = CView::get("sejour_id", "num");
$contexte        = CView::get("contexte", "str");
CView::checkin();

// User
$user  = CMediusers::get();

// Client
$search = new CSearch();
try {
  $search->state();
}
catch (Throwable $e) {
  CAppUI::stepAjax("mod-search-indisponible", UI_MSG_ERROR);
}


$_ref_object = new CSejour();
if ($sejour_id) {
  $sejour = new CSejour();
  $sejour->load($sejour_id);

  CAccessMedicalData::logAccess($sejour);

  $_ref_object = $sejour;
  if (!$contexte) {
    $contexte = "pmsi";
  }
}


if ($prescription_id) {
  $prescription = new CPrescription();
  $prescription->load($prescription_id);
  $prescription->loadRefObject();
  $_ref_object = $prescription->_ref_object;
  if (!$contexte) {
    $contexte = ($user->isPraticien()) ? "prescription" : "pharmacie";
  }
}

$favoris             = new CSearchThesaurusEntry();
$favoris_sans_cibles = new CSearchThesaurusEntry();
$targets             = new CSearchTargetEntry();
$actes_ccam          = array();
$diags_cim           = array();
$results             = array();
$tab_favoris         = array();

$date  = CMbDT::date("-1 month");
$types = array();
$group = CGroups::loadCurrent();
if ($handled = CAppUI::conf("search active_handler active_handler_search_types", $group)) {
  $types = explode("|", $handled);
}

// On récupère les favoris
if ($_ref_object instanceof CSejour) {
  $date = CMbDT::format($_ref_object->entree_reelle, "%Y-%m-%d");
  /** @var  $_ref_object CSejour */
  // actes CCAM du séjour
  foreach ($_ref_object->loadRefsActesCCAM() as $_ccam) {
    $diags_actes[] = $_ccam->code_acte;
  }

  // actes CCAM du l'intervention
  foreach ($_ref_object->loadRefsOperations() as $_op) {
    foreach ($_op->loadRefsActesCCAM() as $_ccam) {
      $diags_actes[] = $_ccam->code_acte;
    }
  }

  if ($_ref_object->DP || $_ref_object->DR) {
    if ($_ref_object->DP) {
      $diags_actes[] = $_ref_object->DP;
    }
    if ($_ref_object->DR) {
      $diags_actes[] = $_ref_object->DR;
    }
  }

  foreach ($_ref_object->loadDiagnosticsAssocies(false) as $_das) {
    $diags_actes[] = $_das;
  }

  $_ref_object->loadRefPrescriptionSejour();
  $meds = $_ref_object->_ref_prescription_sejour->loadRefsLinesMed();
  foreach ($_ref_object->_ref_prescription_sejour->_ref_prescription_lines as $_med) {
    if ($_med->atc) {
      $arbre         = $_med->atc;
      $diags_actes[] = $_med->atc;
      do {
        $med   = new CMedicamentClasseATC();
        $arbre = $med->getCodeNiveauSup($arbre);
        if ($arbre) {
          $diags_actes[] = $arbre;
        }
      } while ($arbre);
    }
  }

  $mix = $_ref_object->_ref_prescription_sejour->loadRefsPrescriptionLineMixes();
  foreach ($_ref_object->_ref_prescription_sejour->_ref_prescription_line_mixes as $_mix) {
    foreach ($_mix->loadRefsLines() as $item) {
      if ($item->atc) {
        $arbre         = $item->atc;
        $diags_actes[] = $item->atc;
        do {
          $med   = new CMedicamentClasseATC();
          $arbre = $med->getCodeNiveauSup($arbre);
          if ($arbre) {
            $diags_actes[] = $arbre;
          }
        } while ($arbre);
      }
    }
  }

  // récupération des favoris avec cibles pour le dossier de soins
  if (isset($diags_actes)) {
    $diags_actes           = array_unique($diags_actes);
    $list                  = array("CCodeCIM10", "CCodeCCAM", "CMedicamentClasseATC");
    $where["object_class"] = " " . CSQLDataSource::prepareIn($list);
    $where["object_id"]    = " " . CSQLDataSource::prepareIn($diags_actes);
    // TODO le loadList va charger n'importe quel objet, il faut séparer le prepareIn des identifiants par object_class
    $targets               = $targets->loadList($where);
    $tab_favoris_id = array();
    foreach ($targets as $_target) {
      /** @var  $_target CSearchTargetEntry */
      $tab_favoris_id[] = $_target->search_thesaurus_entry_id;
    }
    $whereFavoris["search_thesaurus_entry_id"] = CSQLDataSource::prepareIn(array_unique($tab_favoris_id));
    $whereFavoris["contextes"]                 = CSQLDataSource::prepareIn(array("generique", $contexte));

    $whereFavoris["function_id"] = " IS NULL";
    $whereFavoris["group_id"]    = " IS NULL";
    $whereFavoris["user_id"]     = "= '$user->_id'";
    $tab_favoris_user            = $favoris->loadList($whereFavoris);

    unset($whereFavoris["user_id"]);
    $function_id                 = $user->loadRefFunction()->_id;
    $whereFavoris["function_id"] = " = '$function_id'";
    $tab_favoris_function        = $favoris->loadList($whereFavoris);

    unset($whereFavoris["function_id"]);
    $group_id                 = $user->loadRefFunction()->group_id;
    $whereFavoris["group_id"] = " = '$group_id'";
    $tab_favoris_group        = $favoris->loadList($whereFavoris);

    $tab_favoris = $tab_favoris_user + $tab_favoris_function + $tab_favoris_group;
  }

  // récupération des favoris sans cibles avec search_auto à "oui"
  $whereFavorisSansCibles["contextes"]   = CSQLDataSource::prepareIn(array("generique", $contexte));
  $whereFavorisSansCibles["function_id"] = " IS NULL";
  $whereFavorisSansCibles["group_id"]    = " IS NULL";
  $whereFavorisSansCibles["user_id"]     = "= '$user->_id'";
  $whereFavorisSansCibles["search_auto"] = " LIKE '1'";
  $tab_favoris_user_sans_cibles          = $favoris_sans_cibles->loadList($whereFavorisSansCibles);

  unset($whereFavorisSansCibles["user_id"]);
  $function_id                           = $user->loadRefFunction()->_id;
  $whereFavorisSansCibles["function_id"] = " = '$function_id'";
  $tab_favoris_function_sans_cibles      = $favoris_sans_cibles->loadList($whereFavorisSansCibles);

  unset($whereFavorisSansCibles["function_id"]);
  $group_id                           = $user->loadRefFunction()->group_id;
  $whereFavorisSansCibles["group_id"] = " = '$group_id'";
  $tab_favoris_group_sans_cibles      = $favoris->loadList($whereFavorisSansCibles);

  $tab_favoris += $tab_favoris_user_sans_cibles + $tab_favoris_function_sans_cibles + $tab_favoris_group_sans_cibles;
}

// On effectue la recherche automatique
if (isset($tab_favoris)) {
  try {
    $results = $search->searchAuto($tab_favoris, $_ref_object);
  }
  catch (Exception $e) {
    CAppUI::displayAjaxMsg("search-not-connected", UI_MSG_ERROR);
    CApp::log("Log from vw_search_auto", $e->getMessage());
  }
}

// Récupération des rss items pour le marquage pmsi (preuves de recherche PMSI)
$rss_items = array();
$items     = array();
if ($contexte == "pmsi" && CModule::getActive("atih")) {
  $rss            = new CRSS();
  $rss->sejour_id = $sejour_id;
  $rss->loadMatchingObject();
  $rss_items = $rss->loadRefsSearchItems();

  foreach ($rss_items as $_items) {
    $items[] = $_items->search_class . "-" . $_items->search_id;
  }
}

$smarty = new CSmartyDP();
$smarty->assign("sejour", $_ref_object);
$smarty->assign("sejour_id", $_ref_object->_id);
$smarty->assign("results", $results);
$smarty->assign("date", $date);
$smarty->assign("types", $types);
$smarty->assign("contexte", $contexte);
$smarty->assign("rss_items", $rss_items);
$smarty->assign("items", $items);
$smarty->assign("obfuscate", CAppUI::conf("search obfuscation_body"));

$smarty->display("vw_search_auto.tpl");
