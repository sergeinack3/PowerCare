<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CPrestaSSR;

CCanDo::checkRead();
$code = CView::get("code_ssr", "str", true);
$type = CView::get("type_ssr", "enum list|all|dietetique|neuropsychologie|logopedie|ergotherapie|physiotherapie|psychotherapie|psychomotricite|sport|artherapie|massotherapie default|all", true);
$page = CView::get("page_ssr", "num default|0");
CView::checkin();

$step       = 20;
$presta_ssr = new CPrestaSSR();

// recuperation prestation SSR
$where = array();
if ($type != 'all') {
  $where[] = "type = '$type'";
}

if ($code) {
  $where[] = "code LIKE '%$code%'";
}

$order = "type, code";
$limit = "$page, $step";

$prestas = $presta_ssr->loadRefsPrestationsSSR($where, $order, $limit);

// count rows
$total = $presta_ssr->countPrestationsSSR($where, $order);

asort($presta_ssr->_specs['type']->_list);

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("presta_ssr", $presta_ssr);
$smarty->assign("prestas", $prestas);
$smarty->assign("page", $page);
$smarty->assign("step", $step);
$smarty->assign("code", $code);
$smarty->assign("type", $type);
$smarty->assign("total", $total);
$smarty->display("vw_cpresta_ssr");
