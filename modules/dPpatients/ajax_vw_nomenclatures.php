<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkRead();
$object_guid = CView::get("object_guid", "str");
CView::checkin();

//smarty
$smarty = new CSmartyDP();
$smarty->assign("object_guid", $object_guid);
$smarty->display("inc_vw_nomenclatures");

