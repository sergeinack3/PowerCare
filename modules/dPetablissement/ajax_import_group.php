<?php
/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Forms\CExClassImport;

CCanDo::checkAdmin();

$uid      = CView::get("uid", 'str');
$service  = CView::get("service", 'str');
$function = CView::get("function", 'str');
$user     = CView::get("user", 'str');
$bloc     = CView::get("bloc", 'str');
$salle    = CView::get("salle", 'str');
$uf       = CView::get("uf", 'str');

CView::checkin();

$uid = preg_replace('/[^\d]/', '', $uid);

$temp = CAppUI::getTmpPath("group_import");
$file = "$temp/$uid";

if (!file_exists($file)) {
  CAppUI::stepAjax("CFile-not-exists", UI_MSG_ERROR, $file);
}

$data = array();

try {
  $import = new CExClassImport($file);

  /** @var DOMElement $group */
  // Etablissements -------
  $group      = $import->getElementsByClass("CGroups")->item(0);
  $group_name = $import->getNamedValueFromElement($group, "text");

  if ($service == "true") {
    $data["CService"]   = $import->getObjectsList("CService", "nom");
  }

  if ($function == "true") {
    $data["CFunctions"] = $import->getObjectsList("CFunctions", "text");
  }

  if ($user == "true") {
    $data["CUser"]           = $import->getObjectsList("CUser", "user_username", true, false, true);
  }

  if ($bloc == "true") {
    $data["CBlocOperatoire"]     = $import->getObjectsList("CBlocOperatoire", "nom");
  }

  if ($salle == "true") {
    $data["CSalle"]              = $import->getObjectsList("CSalle", "nom");
  }

  if ($uf == "true") {
    $data["CUniteFonctionnelle"] = $import->getObjectsList("CUniteFonctionnelle", "libelle");
  }
} catch (Exception $e) {
  CAppUI::stepAjax($e->getMessage(), UI_MSG_ERROR);
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("group_name", $group_name);
$smarty->assign("uid", $uid);
$smarty->assign("data", $data);
$smarty->display("inc_import_group.tpl");