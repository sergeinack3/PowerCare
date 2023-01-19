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
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CTarif;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$user = CMediusers::get();

$is_praticien = $user->isProfessionnelDeSante();

$prat_id = CView::get("prat_id", "ref class|CMediusers" . ($is_praticien ? " default|$user->_id" : ""));

CView::checkin();

$listPrat = array();
if ($is_praticien) {
  $listPrat = CConsultation::loadPraticiensCompta($user->user_id);
}
else {
  $listPrat = CConsultation::loadPraticiensCompta();
}

$prat = CMediusers::get($prat_id);

if ($prat_id && !in_array($prat_id, array_keys($listPrat))) {
  $prat = new CMediusers();
}

$prat->loadRefFunction();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("user"    , $user);
$smarty->assign("prat"    , $prat);
$smarty->assign("listPrat", $listPrat);
$smarty->assign("tarif"   , new CTarif());

$smarty->display("vw_edit_tarifs.tpl");