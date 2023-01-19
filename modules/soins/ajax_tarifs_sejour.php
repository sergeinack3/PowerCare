<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CTarif;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$consult_id = CView::get("consult_id", 'ref class|CConsultation');
$sejour_id  = CView::get("sejour_id", 'ref class|CSejour');
$chir_id    = CView::get("chir_id", 'ref class|CMediusers');
$type_codes = CView::get('type_codes', 'enum list|ccam|ngap|lpp');

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$user = CMediusers::get();

if ($sejour->_id) {
  $sejour->loadRefPraticien();
  $sejour->loadRefsActes();
  $sejour->updateFormFields();
  $sejour->bindTarif();
  $sejour->_datetime = CMbDT::dateTime();

  $chir = new CMediusers();
  if ($chir_id) {
    $chir->load($chir_id);
  }
  elseif ($user->_id != $sejour->praticien_id && $user->isProfessionnelDeSante()) {
    $chir = $user;
  }
  else {
    $chir = $sejour->_ref_praticien;
  }

  // Récupération des tarifs
  $tarifs = CTarif::loadTarifsUser($chir, null, $type_codes);

  $consult = new CConsultation();
  if ($consult_id) {
    $consult->load($consult_id);
  }

  CAccessMedicalData::logAccess($consult);

  $smarty = new CSmartyDP();

  $smarty->assign("consult", $consult);
  $smarty->assign("sejour", $sejour);
  $smarty->assign("tarifs", $tarifs);
  $smarty->display("inc_tarifs_sejour");
}
