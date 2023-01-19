<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();
/**
 * Modification de grossesse
 */
$user = CMediusers::get();
$user->isProfessionnelDeSante();

// vars
$grossesse_id   = CView::get("grossesse_id", "ref class|CGrossesse");
$parturiente_id = CView::get("parturiente_id", "ref class|CPatient", true);
$operation_id   = CView::get("operation_id", "ref class|COperation");

// options
$with_buttons  = CView::get("with_buttons", "bool default|0"); // see buttons at the right panel
$standalone    = CView::get("standalone", "bool default|0");       // embedded in a requestUpdate for example
$creation_mode = CView::get("creation_mode", "bool default|1");
CView::checkin();

CAccessMedicalData::logAccess("COperation-$operation_id");

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);
$grossesse->loadRefGroup();
$grossesse->getDateAccouchement();
$grossesse->loadRefsNaissances();
$grossesse->loadRefsNotes();

if (!$grossesse->_id) {
    $grossesse->parturiente_id = $parturiente_id;
}

$patient = $grossesse->loadRefParturiente();
$patient->loadIPP($grossesse->group_id);
$patient->loadRefsCorrespondants();
$patient->loadRefsCorrespondantsPatient();

if (!$operation_id) {
  $grossesse->loadLastSejour();

  if ($grossesse->_ref_last_sejour) {
    $grossesse->_ref_last_sejour->loadRefsOperations();
    if ($grossesse->_ref_last_sejour->_ref_last_operation->_id) {
      $operation_id = $grossesse->_ref_last_sejour->_ref_last_operation->_id;
    }
  }
}

/**
 * // sejour & last grossesse
 * $sejour_id = CValue::get("sejour_id");
 * $sejour = new CSejour();
 * $sejour->load($sejour_id);
 **/

$listPrat = CConsultation::loadPraticiens(PERM_EDIT);

$smarty = new CSmartyDP();
$smarty->assign("grossesse", $grossesse);
$smarty->assign("with_buttons", $with_buttons);
$smarty->assign("prats", $listPrat);
$smarty->assign("user", $user);
$smarty->assign("standalone", $standalone);
$smarty->assign("creation_mode", $creation_mode);
$smarty->assign("operation_id", $operation_id);
$smarty->display("inc_edit_grossesse");
