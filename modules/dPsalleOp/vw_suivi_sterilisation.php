<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkRead();

$blocs_ids = CView::get("blocs_ids", 'str', true);
$date_session = CAppUI::pref("suivisalleAutonome") ? false : true;
$date_suivi = CView::get('date', 'date default|now', $date_session);

CView::checkin();

/** @var CBlocOperatoire[] $listBlocs */
$listBlocs  = CGroups::loadCurrent()->loadBlocs(PERM_READ, null, "nom", array("actif" => "= '1'"));

$dmi_active = CModule::getActive("dmi") && CAppUI::gconf("dmi CDM active");

$smarty = new CSmartyDP();

$smarty->assign("blocs_ids" , $blocs_ids);
$smarty->assign("blocs"     , $listBlocs);
$smarty->assign("first_bloc", reset($listBlocs));
$smarty->assign("date"      , $date_suivi);
$smarty->assign("view_light", 1);
$smarty->assign("dmi_active", $dmi_active);

$smarty->display("vw_suivi_salles.tpl");
