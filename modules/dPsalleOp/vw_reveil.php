<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Imeds\CImeds;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$date    = CView::get("date", "date default|now", true);
$bloc_id = CView::get("bloc_id", "ref class|CBlocOperatoire", true);
$sspi_id = CView::get("sspi_id", "ref class|CSSPI", true);

CView::checkin();

$group = CGroups::loadCurrent();

$modif_operation = CCanDo::edit() || $date >= CMbDT::date();
$blocs_list = $group->loadBlocs(PERM_READ, true, "nom", array("actif" => "= '1'"));

$bloc = new CBlocOperatoire();
if ((!$bloc->load($bloc_id) || $bloc->group_id != $group->_id) && count($blocs_list)) {
  $bloc = reset($blocs_list);
}

$bloc->loadRefsSSPIs();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("date"            , $date);
$smarty->assign("modif_operation" , $modif_operation);
$smarty->assign("blocs_list"      , $blocs_list);
$smarty->assign("bloc"            , $bloc);
$smarty->assign("bloc_id"         , $bloc_id);
$smarty->assign("sspi_id"         , $sspi_id);
$smarty->assign("isImedsInstalled", (CModule::getActive("dPImeds") && CImeds::getTagCIDC($group)));
$smarty->assign("is_anesth"       , CMediusers::get()->isAnesth());

$smarty->display("vw_reveil");
