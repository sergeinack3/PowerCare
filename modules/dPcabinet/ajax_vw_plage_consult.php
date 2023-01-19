<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$plage_id = CView::getRefCheckRead("plage_id", "ref class|CPlageconsult");

CView::checkin();

$plage = new CPlageconsult();
$plage->load($plage_id);
$plage->loadRefsNotes();

$plage->loadRefChir()->loadRefFunction();
$plage->loadRefRemplacant()->loadRefFunction();

$consultations = $plage->loadRefsConsultations();
$patients = CStoredObject::massLoadFwdRef($consultations, "patient_id");
CPatient::massCountPhotoIdentite($patients);

foreach ($consultations as $_consult) {
  $_consult->loadRefPatient()->loadRefPhotoIdentite();
}

$plage->loadDisponibilities();

// smarty
$smarty = new CSmartyDP();
$smarty->assign("object", $plage);
$smarty->display("inc_vw_plage_consult.tpl");
