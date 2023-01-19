<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Ccam\CCodeNGAP;

CCanDo::check();

$input_field  = CView::request("input_field", "str default|code_ngap");
$code = CView::request($input_field, 'str');

CView::checkin();
CView::enableSlave();

$codes = CCodeNGAP::search($code);

$smarty = new CSmartyDP();

$smarty->assign('codes', $codes);
$smarty->assign("nodebug" , true);

$smarty->display("inc_ngap_autocomplete.tpl");
