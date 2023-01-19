<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\OxLaboClient\OxLaboClientHandler;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceHTTP;

CCanDo::checkRead();

$file_id       = CView::get("id", "ref class|CFile");
$name_readonly = CView::get("name_readonly", "bool default|0");
$object_class  = CView::get("object_class", "str");
$object_id     = CView::get("object_id", "ref class|$object_class");

CView::checkin();

$file = new CFile();
$file->load($file_id);
$file->canDo();

$alerts_new_result     = [];
$alerts_anormal_result = [];
//Récupération des alertes OxLabo
$source_labo = CExchangeSource::get(
    "OxLabo" . CGroups::loadCurrent()->_id,
    CSourceHTTP::TYPE,
    false,
    "OxLaboExchange",
    false
);

$object = null;
if ($object_class == "CSejour" && CModule::getActive("OxLaboClient") && $source_labo->active) {
    $object                = CSejour::findOrFail($object_id);
    $labo_handler          = new OxLaboClientHandler();
    $alerts_anormal_result = $labo_handler->getAlerteAnormalForSejours([$object], "file_id");

    $alerts_new_result = $labo_handler->getAlertNewResultForSejours([$object], "file_id");
}

$smarty = new CSmartyDP();
$smarty->assign("_file", $file);
$smarty->assign("object_id", $object_id);
$smarty->assign("object_class", $object_class);
$smarty->assign("name_readonly", $name_readonly);
$smarty->assign("alerts_anormal_result", $alerts_anormal_result);
$smarty->assign("alerts_new_result", $alerts_new_result);
$smarty->assign("object", $object);

$smarty->display("inc_widget_line_file.tpl");
