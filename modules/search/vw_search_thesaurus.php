<?php
/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Search\CSearch;
use Ox\Mediboard\Search\CSearchThesaurusEntry;

CCanDo::checkRead();

$date = CMbDT::date("-1 month");
$user = CMediusers::get();
$user->loadRefFunction()->loadRefGroup();


$entry          = new CSearchThesaurusEntry();
$entry->user_id = "$user->_id";
$thesaurus      = $entry->loadMatchingList();
foreach ($thesaurus as $_thesaurus) {
  $_thesaurus->loadRefsTargets();
}

$types = array();
$group = CGroups::loadCurrent();
if ($handled = CAppUI::conf("search active_handler active_handler_search_types", $group)) {
  $types = explode("|", $handled);
}

$contextes = CSearch::$contextes;

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("date", $date);
$smarty->assign("start", 0);
$smarty->assign("results", array());
$smarty->assign("time", 0);
$smarty->assign("nbresult", 0);
$smarty->assign("thesaurus", $thesaurus);
$smarty->assign("entry", $entry);
$smarty->assign("types", $types);
$smarty->assign("user", $user);
$smarty->assign("contextes", $contextes);
$smarty->display("vw_search_thesaurus.tpl");