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
use Ox\Mediboard\Search\CSearch;
use Ox\Mediboard\Search\CSearchHistory;
use Ox\Mediboard\Search\CSearchThesaurusEntry;

CCanDo::checkRead();
$thesaurus_entry_id = CView::get("thesaurus_entry_id", "ref class|CSearchThesaurusEntry");
$search_history_id  = CView::get("search_history_id", "ref class|CSearchHistory");
$contexte           = CView::get("contexte", "str default|classique");
CView::checkin();

// thesaurus
$thesaurus = null;
if (!is_null($thesaurus_entry_id)) {
  $thesaurusEntry = new CSearchThesaurusEntry();
  $thesaurusEntry->load($thesaurus_entry_id);
  $thesaurus = array(
    'words'      => $thesaurusEntry->entry,
    'types'      => $thesaurusEntry->types,
    'agregation' => $thesaurusEntry->agregation,
    'fuzzy'      => $thesaurusEntry->fuzzy,
  );
}

// history
$history = null;
if (!is_null($search_history_id)) {
  $history = new CSearchHistory();
  $history->load($search_history_id);
  $history = array(
    'words'      => $history->entry,
    'types'      => $history->types,
    'agregation' => $history->agregation,
    'fuzzy'      => $history->fuzzy,
  );
}

// on récupère les types en fonction de la config établissement.
$group = CGroups::loadCurrent();
$types = null;
if ($handled = CAppUI::conf("search active_handler active_handler_search_types", $group)) {
  $types = explode("|", $handled);
}

// Client
$search = new CSearch();
try {
  $search->state();
}
catch (Throwable $e) {
  CAppUI::stepAjax("mod-search-indisponible", UI_MSG_ERROR);
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("types", $types);
$smarty->assign("request_size", CSearch::REQUEST_SIZE);
$smarty->assign("thesaurus", $thesaurus);
$smarty->assign("history", $history);
$smarty->assign("contexte", $contexte);
$smarty->assign("sejour_id", "");
$smarty->display("vw_search.tpl");

