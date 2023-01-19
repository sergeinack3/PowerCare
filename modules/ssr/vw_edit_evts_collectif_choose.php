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
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Ssr\CEvenementSSR;

global $m;

CCanDo::checkRead();
$sejour_id = CView::get("sejour_id", "ref class|CSejour");
$evts_ids  = CView::get("evts_ids", "str");
$evts_ids  = json_decode(utf8_encode(stripslashes($evts_ids)), true);
CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadRefPrescriptionSejour()->loadRefsLinesElement();

$evenements = array();
foreach ($evts_ids as $_id_evt => $_evt) {
  if ($_evt["checked"]) {
    $evenement = new CEvenementSSR();
    $evenement->load($_id_evt);
    $evenement->loadRefsEvenementsSeance();
    $evenement->loadRefPrescriptionLineElement()->loadRefElement()->loadRefsCodesSSR();
    $evenement->loadRefTherapeute()->loadRefFunction();
    $evenements[$_id_evt] = $evenement;
  }
}

$smarty = new CSmartyDP();
$smarty->assign("evenements", $evenements);
$smarty->assign("sejour", $sejour);
$smarty->display("vw_edit_evts_collectif_choose");
