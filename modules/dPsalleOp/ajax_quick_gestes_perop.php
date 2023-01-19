<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\SalleOp\CAnesthPerop;
use Ox\Mediboard\SalleOp\CGestePerop;

CCanDo::checkRead();
$operation_id = CView::get("operation_id", "ref class|COperation");
$datetime     = CView::get("datetime", "dateTime default|now");
CView::checkin();

$filtre = new CGestePerop();
$filtre->_datetime = $datetime;

$interv = new COperation;
$interv->load($operation_id);

CAccessMedicalData::logAccess($interv);

$interv->loadComplete();

$group    = CGroups::loadCurrent();
$user     = CMediusers::get();
$function = $user->loadRefFunction();

$geste = new CGestePerop();

// user
$geste_by_chapitre_user = array();
$geste_by_chapitre_user = $geste->loadGestesByChapitre("user_id", $user->_id);

// function
$geste_by_chapitre_function = array();
$geste_by_chapitre_function = $geste->loadGestesByChapitre("function_id", $function->_id);

// group
$geste_by_chapitre_group = array();
$geste_by_chapitre_group = $geste->loadGestesByChapitre("group_id", $group->_id);

$evenement = new CAnesthPerop();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("gestes_user"    , $geste_by_chapitre_user);
$smarty->assign("gestes_function", $geste_by_chapitre_function);
$smarty->assign("gestes_group"   , $geste_by_chapitre_group);
$smarty->assign("operation"      , $interv);
$smarty->assign("evenement"      , $evenement);
$smarty->assign("filtre"         , $filtre);
$smarty->display("inc_quick_gestes_perop");
