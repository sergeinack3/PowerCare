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
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Medicament\CMedicamentClasseATC;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Pmsi\CCIM10;
use Ox\Mediboard\Search\CSearch;
use Ox\Mediboard\Search\CSearchThesaurusEntry;

CCanDo::checkRead();

$user = CMediusers::get();
$user->loadRefFunction()->loadRefGroup();

$entry = new CSearchThesaurusEntry();
$ds    = $entry->getDS();

$where = array();
$types = array();
$group = CGroups::loadCurrent();

if ($handled = CAppUI::conf("search active_handler active_handler_search_types", $group)) {
  $types = explode("|", $handled);
}

$contextes          = CValue::get("contextes");
$contextes          = ($contextes) ? $contextes : CSearch::$contextes;
$where["contextes"] = $entry->_spec->ds->prepareIn($contextes);
$where[]            =
  "(user_id = '$user->_id' OR function_id = '$user->function_id' OR group_id = '{$user->_ref_function->group_id}')";

$thesaurus_user     = [];
$thesaurus_function = [];
$thesaurus_group    = [];

$thesaurus    = $entry->loadList($where);
$nb_thesaurus = $entry->countList($where);

// Chargement des cibles des favoris de type classe ATC.
foreach ($thesaurus as $_thesaurus) {
  /** @var $_thesaurus  CSearchThesaurusEntry */
  $_thesaurus->loadRefsTargets();
  foreach ($_thesaurus->_cim_targets as $_target) {
    $cim10       = new CCIM10();
    $code        = $_target->_ref_target->code;
    $cim10->code = $code;
    $cim10->load();
    $_target->_ref_target->libelle = $cim10->libelle_court;
  }

  foreach ($_thesaurus->_atc_targets as $_target) {
    foreach ($_target->_ref_target as $_atc) {
      $object            = new CMedicamentClasseATC();
      $_target->_libelle = $object->getLibelle($_target->object_id);
    }
  }

  // tri suivant la portée du favoris
  if ($_thesaurus->group_id) {
    $thesaurus_group[] = $_thesaurus;
  }
  else {
    if ($_thesaurus->function_id) {
      $thesaurus_function[] = $_thesaurus;
    }
    else {
      $thesaurus_user[] = $_thesaurus;
    }
  }
}

$smarty = new CSmartyDP();
$smarty->assign("thesaurus", $thesaurus);
$smarty->assign("thesaurus_user", $thesaurus_user);
$smarty->assign("thesaurus_function", $thesaurus_function);
$smarty->assign("thesaurus_group", $thesaurus_group);
$smarty->assign("entry", $entry);
$smarty->assign("types", $types);
$smarty->assign("user", $user);
$smarty->assign("nbThesaurus", $nb_thesaurus);
$smarty->display("inc_search_thesaurus_entry.tpl");