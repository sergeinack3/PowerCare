<?php
/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Medicament\CMedicamentClasseATC;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Pmsi\CCIM10;
use Ox\Mediboard\Search\CSearchThesaurusEntry;

CCanDo::checkRead();

$thesaurus_entry_id = CView::get("thesaurus_entry", "text");
$search_agregation  = CView::get("search_agregation", "str");
$search_fuzzy       = CView::get("search_fuzzy", "str");
$search_body        = CView::get("search_body", "str");
$search_user_id     = CView::get("search_user_id", "ref class|CUser");
$search_types       = CView::get("search_types", "str");
$search_contexte    = CView::get("search_contexte", "str");
CView::checkin();


$thesaurus_entry = new CSearchThesaurusEntry();
if ($thesaurus_entry_id) {
  $thesaurus_entry->load($thesaurus_entry_id);
  $search_types = explode("|", $thesaurus_entry->types);
  $thesaurus_entry->loadRefsTargets();

  foreach ($thesaurus_entry->_cim_targets as $_target) {
    $cim10       = new CCIM10();
    $code        = $_target->_ref_target->code;
    $cim10->code = $code;
    $cim10->load();
    $_target->_ref_target->libelle = $cim10->libelle_court;
  }

  foreach ($thesaurus_entry->_atc_targets as $_target) {
    foreach ($_target->_ref_target as $_atc) {
      $object            = new CMedicamentClasseATC();
      $_target->_libelle = $object->getLibelle($_target->object_id);
    }
  }
}
else {
  $thesaurus_entry->agregation = $search_agregation;
  $thesaurus_entry->fuzzy      = $search_fuzzy;
  $thesaurus_entry->entry      = $search_body;
  $thesaurus_entry->user_id    = $search_user_id;
  $thesaurus_entry->types      = is_array($search_types) ? implode(" ", $search_types) : explode(" ", $thesaurus_entry->types);
  $thesaurus_entry->contextes  = $search_contexte;
}

$types = array();
$group = CGroups::loadCurrent();
if ($handled = CAppUI::conf("search active_handler active_handler_search_types", $group)) {
  $types = explode("|", $handled);
}

$user = new CMediusers();
$user->load($thesaurus_entry->user_id);
$user->loadRefFunction()->loadRefGroup();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("thesaurus_entry", $thesaurus_entry);
$smarty->assign("search_types", $search_types);
$smarty->assign("types", $types);
$smarty->assign("user_thesaurus", $user);
$smarty->display("vw_addedit_entry.tpl");