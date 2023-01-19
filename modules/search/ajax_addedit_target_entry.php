<?php
/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Medicament\CMedicamentClasseATC;
use Ox\Mediboard\Pmsi\CCIM10;
use Ox\Mediboard\Search\CSearchTargetEntry;
use Ox\Mediboard\Search\CSearchThesaurusEntry;

CCanDo::checkRead();

$thesaurus_entry_id = CView::get("thesaurus_entry_id", 'ref class|CSearchThesaurusEntry');

CView::checkin();

$target             = new CSearchTargetEntry();
$thesaurus_entry    = new CSearchThesaurusEntry();
$thesaurus_entry->load($thesaurus_entry_id);
$thesaurus_entry->loadRefsTargets();

// CIM10
foreach ($thesaurus_entry->_cim_targets as $_target) {
  $cim10       = new CCIM10();
  $code        = $_target->_ref_target->code;
  $cim10->code = $code;
  $cim10->load();
  $_target->_ref_target->libelle = $cim10->libelle_court;
}

// ATC
foreach ($thesaurus_entry->_atc_targets as $_target) {
  foreach ($_target->_ref_target as $_atc) {
    $object            = new CMedicamentClasseATC();
    $_target->_libelle = $object->getLibelle($_target->object_id);
  }
}

$smarty = new CSmartyDP();
$smarty->assign("thesaurus_entry_id", $thesaurus_entry_id);
$smarty->assign("thesaurus_entry", $thesaurus_entry);
$smarty->assign("target", $target);
$smarty->display("vw_addedit_target.tpl");