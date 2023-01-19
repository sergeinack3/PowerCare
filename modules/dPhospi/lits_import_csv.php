<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Hospi\CCSVImportInfrastructure;

CCanDo::checkAdmin();

$file = isset($_FILES['import']) ? $_FILES['import'] : null;

$results = array();
if ($file) {
  $import = new CCSVImportInfrastructure($file['tmp_name']);
  $import->import();
  $results = $import->getResults();
}

CAppUI::callbackAjax('$("systemMsg").insert', CAppUI::getMsg());

$smarty = new CSmartyDP();
$smarty->assign("results", $results);
$smarty->display("lits_import_csv.tpl");
