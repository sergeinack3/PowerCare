<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$sejour_id = CView::get("sejour_id", "ref class|CSejour");

CView::checkin();
CView::enableSlave();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$bebe = $sejour->loadRefPatient();

$maman = $sejour->loadRefNaissance()->loadRefSejourMaman()->loadRefPatient();

$dossier_medical = $maman->loadRefDossierMedical();

$dossier_medical->loadRefsAntecedents();

$smarty = new CSmartyDP();

$smarty->assign("maman", $maman);
$smarty->assign("bebe", $bebe);

$smarty->display("inc_list_atcd_maman.tpl");