<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Ssr\CPrestaSSR;

CCanDo::checkRead();
$code            = CView::get("code", "str");
$code_prestas    = CView::get("code_prestas", "str");
$code_presta_ssr = CView::get("code_presta_ssr", "str");
$all             = CView::get("all", "bool default|0");
CView::checkin();

if ($code) {
  $code_presta_ssr = $code;
}

if ($code_prestas) {
  $code_presta_ssr = $code_prestas;
}

CView::enableSlave();

$presta_ssr = new CPrestaSSR();
$ds         = $presta_ssr->getDS();
$where      = [];

// Display all if there is no codes
if ($code_presta_ssr) {
  $prepared_code = $ds->prepareLike("%$code_presta_ssr%");
  $where[]       = "code $prepared_code OR libelle $prepared_code";
}

if (!$all) {
  $code_affectations = CMediusers::get()->loadRefFunction()->loadRefsCodesAffectations();
  $code_list         = CMbArray::pluck($code_affectations, "code");

  if ($code_list) {
    $where[] = "code " . CSQLDataSource::prepareIn($code_list) . " OR libelle " . CSQLDataSource::prepareIn($code_list);
  }
}


$prestas = $presta_ssr->loadRefsPrestationsSSR($where);

$smarty = new CSmartyDP();
$smarty->assign("prestas", $prestas);
$smarty->display("inc_presta_ssr_autocomplete");
