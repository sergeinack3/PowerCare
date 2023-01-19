<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Ssr\CCodeAffectation;

CCanDo::checkAdmin();
$function_id = CView::get("function_id", "ref class|CFunctions");
$reload      = CView::get("reload", "bool default|0");
CView::checkin();

$function     = CFunctions::findOrFail($function_id);
$affectations = $function->loadRefsCodesAffectations();

foreach ($affectations as $_affectation) {
  $_affectation->loadRefCode();
}

$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("function", $function);
$smarty->assign("affectations", $affectations);
$smarty->assign("affectation", new CCodeAffectation());

if ($reload) {
  $smarty->display("inc_affectations_code");
}
else {
  $smarty->display("vw_affectations_function");
}
