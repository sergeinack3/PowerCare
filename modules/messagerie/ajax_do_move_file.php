<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Messagerie\CDocumentExterne;

CCanDo::checkEdit();

//get
$document_guid = CView::get("document_guid", 'guid class|CDocumentExterne');
$patient_id = CView::get("patient_id", 'ref class|CPatient');

CView::checkin();

/** @var CDocumentExterne $document */
$document = CMbObject::loadFromGuid($document_guid);
if (!$document->_id) {
  CAppUI::stepAjax("PB");
}

$account = $document->loadRefAccount();
//$praticien = $account->loadRefMediuser();

$file = $document->loadRefFile(true);

$cat = new CFilesCategory();
$cats = $cat->loadListWithPerms();

if (!$file->_id) {
  CAppUI::stepAjax("CBioServeurAccount-msg-no_file_id_spectified_for_moving", UI_MSG_ERROR);
}
$file->loadTargetObject();


//finding patient
$patient = $document->findPatient();

//smarty
$smarty = new CSmartyDP("modules/messagerie");
$smarty->assign("file", $file);
$smarty->assign("file_categories", $cats);
//$smarty->assign("praticien", $praticien);
$smarty->assign("document", $document);
$smarty->assign("guessing_date", $document->document_date);
$smarty->assign("patient", $patient);
$smarty->display("inc_move_file.tpl");