<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CCanDo;

CCanDo::checkRead();

$smarty = new CSmartyDP();
$smarty->assign('errors', []);
$smarty->assign('pending_lines', []);
$smarty->assign('processed_lines', 0);
$smarty->display('vw_import_product_location');