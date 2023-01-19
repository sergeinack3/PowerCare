<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Facturation\CFacture;

CCanDo::checkEdit();
$facture_guid = CView::get("facture_guid", "str");
CView::checkin();

/* @var CFacture $facture*/
$facture = CMbObject::loadFromGuid($facture_guid);
$patient = $facture->loadRefPatient();
$patient->loadRefsCorrespondantsPatient("date_debut DESC, date_fin DESC");
$facture->loadRefPraticien();
$facture->loadRefAssurance();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("patient"       , $patient);
$smarty->assign("facture"       , $facture);
$smarty->display("inc_vw_assurances");