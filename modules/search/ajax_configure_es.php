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
use Ox\Mediboard\Search\CSearch;
use Ox\Mediboard\Search\CSearchIndexing;

CCanDo::checkAdmin();
$error = "";

// Client
$search = new CSearch();
try {
  $search->state();
}
catch (Throwable $e) {
  CAppUI::stepAjax("mod-search-indisponible", UI_MSG_ERROR);
}

// index
$exist_index = $search->existIndex($search->_index);

// tampon
$searchIndexing = new CSearchIndexing();
$ds             = $searchIndexing->getDS();
$where          = array("processed" => " = '0' ");
$nbr_doc        = $searchIndexing->countList($where);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("error", $error);
$smarty->assign("exist_index", $exist_index);
$smarty->assign("index", $search->_index);
$smarty->assign("nbr_doc", $nbr_doc);
$smarty->display("inc_configure_es.tpl");