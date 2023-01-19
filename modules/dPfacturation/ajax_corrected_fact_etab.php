<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();
$group = CGroups::loadCurrent();
$see    = CView::get("see", "bool default|1");
$page   = CView::get("page", "num default|0");
CView::checkin();

$factures_changes = 0;

$ljoin = array();
$ljoin["users_mediboard"] = "`users_mediboard`.`user_id` = facture_cabinet.praticien_id";
$ljoin["functions_mediboard"] = "`functions_mediboard`.`function_id` = users_mediboard.function_id";
$where = array();
$where["facture_cabinet.group_id"] = " <> functions_mediboard.group_id";

$factures = $errors = array();
$nb_factures = 0;
$facture = new CFactureCabinet();
$limit_see = 10;

if ($see) {
  $nb_factures = $facture->countList($where, null, $ljoin);
  $factures = $facture->loadList($where, null, "$page, $limit_see", "facture_id desc", $ljoin);
}
else {
  $factures = $facture->loadList($where, null, 100, "facture_id desc", $ljoin);
  foreach ($factures as $_facture) {
    /* @var CFactureCabinet $_facture*/
    $function = $_facture->loadRefPraticien()->loadRefFunction();
    if ($function->group_id) {
      $_facture->group_id = $function->group_id;
      if ($msg = $_facture->store()) {
        $errors[] = $msg;
        continue;
      }
      $factures_changes++;
    }
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("factures_changes", $factures_changes);
$smarty->assign("factures"        , $factures);
$smarty->assign("see"             , $see);
$smarty->assign("nb_factures"     , $nb_factures);
$smarty->assign("limit_see"       , $limit_see);
$smarty->assign("errors"          , $errors);
$smarty->assign("page"            , $page);

$smarty->display("tools/inc_tools_actions");