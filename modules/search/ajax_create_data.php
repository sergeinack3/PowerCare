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
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Search\CSearch;
use Ox\Mediboard\Search\CSearchIndexing;

CCanDo::checkAdmin();

// Client
$search = new CSearch();
try {
  $search->state();
}
catch (Throwable $e) {
  CAppUI::stepAjax("mod-search-indisponible", UI_MSG_ERROR);
}

// Group
$group          = CGroups::loadCurrent();
$object_classes = array();

if ($handled = CAppUI::conf("search active_handler active_handler_search_types", $group)) {
  $object_classes = explode("|", $handled);
}

$search_indexing = new CSearchIndexing();
$search_indexing->firstIndexingStore($object_classes);

CAppUI::displayAjaxMsg("l'opération en base s'est déroulée avec succès ", UI_MSG_OK);


CApp::rip();

