<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDO::checkRead();

$user = CUser::get();

$now = CMbDT::date();

$filterOp = new COperation();
$filterOp->salle_id      = CValue::getOrSession("salle_id");
$filterOp->_date_min     = CValue::get("_date_min", $now);
$filterOp->_date_max     = CValue::get("_date_max", $now);
$filterOp->_prat_id      = CValue::getOrSession("_prat_id");
$filterOp->_plage        = CValue::getOrSession("_plage");
$filterOp->_ranking      = CValue::getOrSession("_ranking");
$filterOp->_cotation     = CValue::getOrSession("_cotation");
$filterOp->_specialite   = CValue::getOrSession("_specialite");
$filterOp->_codes_ccam   = CValue::getOrSession("_codes_ccam");
$filterOp->_ccam_libelle = CValue::getOrSession("_ccam_libelle");

$filterSejour = new CSejour();
$filterSejour->type = CValue::getOrSession("type");
$filterSejour->ald  = CValue::getOrSession("ald");
$yesterday  = CMbDT::date("-1 day", $now);

$mediuser = new CMediusers();
$listPrat = $mediuser->loadPraticiens(PERM_READ);

$function = new CFunctions();
$listSpec = $function->loadSpecialites(PERM_READ);

// Récupération des salles
$listBlocs = CGroups::loadCurrent()->loadBlocs(PERM_EDIT, true, "nom", array("actif" => "= '1'"), array("actif" => "= '1'"));

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("chir"         , $user->_id);
$smarty->assign("filter"       , $filterOp);
$smarty->assign("filterSejour" , $filterSejour);
$smarty->assign("now"          , $now);
$smarty->assign("yesterday"    , $yesterday);
$smarty->assign("listPrat"     , $listPrat);
$smarty->assign("listSpec"     , $listSpec);
$smarty->assign("listBlocs"    , $listBlocs);

$smarty->display("print_plannings/vw_print_planning");
