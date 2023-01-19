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
use Ox\Core\CView;
use Ox\Mediboard\Search\CSearch;

CCanDo::checkAdmin();
$type_index = CView::get("type_index", "str");
CView::checkin();

// Client
$search = new CSearch();
try {
  $search->state();
}
catch (Throwable $e) {
  CAppUI::stepAjax("mod-search-indisponible", UI_MSG_ERROR);
}

switch ($type_index) {
  case "generique":
    $index = $search->_index;
    break;
  default:
    $index = $search->_index;
    break;
}


// Index
if ($search->existIndex($index)) {
  CAppUI::stepAjax('Index %s déja existant', UI_MSG_WARNING, $index);
}
else {
  try {
    $search->createIndex($index);
    CAppUI::stepAjax("L'index " . $index . " s'est correctement créé", UI_MSG_OK);
  }
  catch (Exception $e) {
    CAppUI::stepAjax("L'index " . $e->getMessage(), UI_MSG_ERROR);
  }
}


CApp::rip();

