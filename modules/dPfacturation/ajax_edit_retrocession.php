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
use Ox\Mediboard\Facturation\CRetrocession;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();
$prat_id = CView::get("prat_id", "ref class|CMediusers", 1);
$retrocession_id = CView::get("retrocession_id", "ref class|CRetrocession", 1);
CView::checkin();

$retro = new CRetrocession();
if ($retrocession_id) {
  $retro->load($retrocession_id);
  $retro->loadRefPraticien();
}
else {
  $retro->praticien_id = $prat_id;
  $retro->type = "montant";
}

$mediuser = new CMediusers();
$listPrat = $mediuser->loadPraticiens();

// Creation du template
$smarty = new CSmartyDP();

$smarty->assign("listPrat",   $listPrat);
$smarty->assign("retrocession",  $retro);

$smarty->display("vw_edit_retrocession");
