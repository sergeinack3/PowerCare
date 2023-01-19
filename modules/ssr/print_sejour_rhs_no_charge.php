<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CRHS;

CCanDo::checkRead();

$sejour_ids  = explode("-", CView::get("sejour_ids", "str"));
$date_monday = CView::get("date_monday", "date");
$all_rhs     = CView::get("all_rhs", "bool");
CView::checkin();

$where["sejour_id"]   = CSQLDataSource::prepareIn($sejour_ids);
$where["date_monday"] = $all_rhs ? ">= '$date_monday'" : "= '$date_monday'";

$order = "sejour_id, date_monday";

$rhs = new CRHS;
/** @var CRHS[] $sejours_rhs */
$sejours_rhs = $rhs->loadList($where, $order);

$sejour = CStoredObject::massLoadFwdRef($sejours_rhs, "sejour_id");
CStoredObject::massLoadBackRefs($sejour, "evenements_ssr", "debut ASC");

foreach ($sejours_rhs as $_rhs) {
  // Dépendances
  $dependances = $_rhs->loadRefDependances();
  if (!$dependances->_id) {
    $dependances->store();
  }
  $dependances->loadRefBilanRHS();
  $sejour = $_rhs->loadRefSejour();
  $_rhs->buildTotaux();

  // Actes de prestations SSR
  $_rhs->loadRefActesPrestationSSR();

  $sejour->loadRefsEvtsSSRSejour();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejours_rhs", $sejours_rhs);
$smarty->assign("read_only", true);

$smarty->display("print_sejour_rhs_no_charge");
