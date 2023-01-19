<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Maternite\CGrossesse;

/**
 * Liste des grossesses en cours du tableau de bord
 */

CCanDo::checkRead();
$date           = CView::get("date", "date default|now");
CView::checkin();

$group = CGroups::loadCurrent();

$date_min = CMbDT::date("-" . CAppUI::conf("maternite CGrossesse min_check_terme", $group) . " DAYS", $date);
$date_max = CMbDT::date("+" . CAppUI::conf("maternite CGrossesse max_check_terme", $group) . " DAYS", $date);

$where = array(
  "grossesse.terme_prevu" => "BETWEEN '$date_min' AND '$date_max'",
  "grossesse.group_id"    => "= '$group->_id' ",
  "grossesse.active"      => "= '1'"
);

$grossesse = new CGrossesse();
/** @var CStoredObject[] $grossesses */
$grossesses = $grossesse->loadList($where);

$patientes = CStoredObject::massLoadFwdRef($grossesses, "parturiente_id");
CStoredObject::massLoadBackRefs($patientes, "bmr_bhre");
CStoredObject::massCountBackRefs($grossesses, "sejours");

$ljoin         = array(
  "plageconsult" => "plageconsult.plageconsult_id = consultation.plageconsult_id"
);
$consultations = CStoredObject::massLoadBackRefs($grossesses, "consultations", "date DESC, heure DESC", null, $ljoin);

CStoredObject::massLoadFwdRef($consultations, "plageconsult_id");

/** @var CGrossesse[] $grossesses */
foreach ($grossesses as $_grossesse) {
  $_grossesse->loadRefParturiente()->updateBMRBHReStatus();
  $_grossesse->countRefSejours();
  $_grossesse->loadRefsConsultations(true);
  $_grossesse->loadLastConsult();
}

$order_prevu = CMbArray::pluck($grossesses, "terme_prevu");
$order_nom = CMbArray::pluck($grossesses, "_ref_parturiente", "nom");
array_multisort(
  $order_prevu, SORT_ASC, // Par terme prévu
  $order_nom, SORT_ASC, // Puis par nom de patiente
  $grossesses
);

$prats = array();

$smarty = new CSmartyDP();

$smarty->assign("grossesses", $grossesses);
$smarty->assign("date", $date);
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);

$smarty->display("inc_tdb_grossesses");