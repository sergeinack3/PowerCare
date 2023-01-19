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
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Prescription\CPrescription;

CCanDo::checkEdit();

$acte_id = CView::get("acte_id", "ref class|CActeNGAP");

CView::checkin();

$acte = new CActeNGAP();
$acte->load($acte_id);

$acte->loadTargetObject();
$prescriptions = CPrescription::loadRefsPrescriptionExternes($acte->_ref_object, null);
$acte->loadRefsFiles();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("acte", $acte);
$smarty->assign("prescriptions", $prescriptions);

$smarty->display("inc_fields_prescription");
