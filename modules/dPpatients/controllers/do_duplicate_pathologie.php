<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPathologie;

CCanDo::checkRead();

$pathologie_id = CView::post("pathologie_id", "ref class|CPathologie");

CView::checkin();

$pathologie = new CPathologie();
$pathologie->load($pathologie_id);
$pathologie->annule = 1;
if ($msg = $pathologie->store()) {
  CAppUI::stepAjax($msg, UI_MSG_WARNING);
}

if ($pathologie->_id && $pathologie->code_cim10) {
    CAppUI::callbackAjax("Snomed.checkCodeExist", $pathologie->code_cim10, $pathologie->_guid);
}

$patho_new           = $pathologie;
$patho_new->_id      = null;
$patho_new->owner_id = CMediusers::get()->_id;
$patho_new->annule   = 0;
if ($msg = $patho_new->store()) {
  CAppUI::stepAjax($msg, UI_MSG_WARNING);
}

$dossier_medical = $patho_new->loadRefDossierMedical();
CAppUI::callbackAjax("Pathologie.edit", $patho_new->_id);

echo CAppUI::getMsg();
CApp::rip();
