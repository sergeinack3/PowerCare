<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkRead();

$printer_id = CView::get("printer_id", "num default|0", true);

CView::checkin();

$smarty = new CSmartyDP();

$smarty->assign("printer_id", $printer_id);

$smarty->display("vw_printers.tpl");
