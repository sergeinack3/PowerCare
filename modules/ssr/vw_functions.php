<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CFunctions;

CCanDo::checkAdmin();
$reload = CView::get("reload", "bool default|0");
$page   = CView::get("page", "num default|0", true); // if reload
CView::checkin();

$function = new CFunctions();

if (!$reload) {
  $functions = $function->loadList(null, "text ASC");
}
else {
  $functions = $function->loadList(null, "text ASC", "$page, 20");
}

$total = $function->countList();

CStoredObject::massLoadBackRefs($functions, "codes_affectations");
foreach ($functions as $_function) {
  $affectations = $_function->loadRefsCodesAffectations();

  foreach ($affectations as $_affectation) {
    $_affectation->loadRefCode();
  }
}

$smarty = new CSmartyDP("modules/ssr");

$smarty->assign("page", $page);
$smarty->assign("step", 20);
$smarty->assign("functions", $functions);
$smarty->assign("total", $total);

if (!$reload) {
  $smarty->display("vw_functions");
}
else {
  $smarty->display("inc_functions");
}
