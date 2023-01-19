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
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Search\CSearchHistory;
use Ox\Mediboard\Search\CSearchThesaurusEntry;

CCanDo::checkRead();

$object_class = CView::get('object_class', 'str');
$field        = CView::get('field', 'str');
$view_field   = CView::get('view_field', 'str');
$input_field  = CView::get('input_field', 'str');
$show_view    = CView::get('show_view', 'str default|false');
$keywords     = CView::get($input_field, 'str');
$limit        = CView::get('limit', 'num default|30');
$contextes    = CView::get('contextes', 'str');
$user_id      = CView::get('user_id', 'ref class|CUser');

CView::checkin();

CView::enableSlave();

// User
$user = new CMediusers();
$user->load($user_id);
$user->loadRefFunction();
$function_id = $user->_ref_function->_id;
$group_id    = $user->_ref_function->group_id;

// KeyWords
if ($keywords == "") {
  $keywords = "%";
}

// History
$history           = new CSearchHistory();
$ds                = $history->_spec->ds;
$where             = array();
$where["contexte"] = $ds->prepareIn($contextes);
$where["user_id"]  = " = $user_id";
$order             = " date desc";
$matches_history   = $history->getAutocompleteList($keywords, $where, $limit, null, $order);
$uniqueHistory     = array();
foreach ($matches_history as $key => $history) {
  $entry = $history->entry;
  if (in_array($entry, $uniqueHistory)) {
    unset($matches_history[$key]);
  }
  else {
    $uniqueHistory[] = $entry;
  }
}


// Thesaurus
$thesaurus            = new CSearchThesaurusEntry();
$ds                   = $thesaurus->_spec->ds;
$where                = array();
$where["contextes"]   = $ds->prepareIn($contextes);
$where["user_id"]     = " = $user_id";
$where["function_id"] = " IS NULL";
$where["group_id"]    = " IS NULL";
$matchesUser          = $thesaurus->getAutocompleteList($keywords, $where, $limit, null);

unset($where["user_id"]);

$matchesFunction = array();
if($function_id){
  $where["function_id"] = " = $function_id";
  $matchesFunction      = $thesaurus->getAutocompleteList($keywords, $where, $limit, null);
}
unset($where["function_id"]);

$matchesGroup = array();
if($group_id){
  $where["group_id"] = " = $group_id";
  $matchesGroup      = $thesaurus->getAutocompleteList($keywords, $where, $limit, null);
}


$matches_thesaurus = $matchesUser + $matchesFunction + $matchesGroup;


// Création du template
$smarty = new CSmartyDP();
$smarty->assign('matches_history', $matches_history);
$smarty->assign('matches_thesaurus', $matches_thesaurus);
$smarty->assign('field', $field);
$smarty->assign('view_field', $view_field);
$smarty->assign('show_view', $show_view);
$smarty->assign('nodebug', false);
$smarty->assign("input", "");
$smarty->assign("user_id", $user_id);
$smarty->assign("function_id", $function_id);
$smarty->assign("group_id", $group_id);

$smarty->display('inc_search_autocomplete.tpl');
