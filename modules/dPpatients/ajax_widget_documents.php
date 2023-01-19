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
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$object_class = CView::get("object_class", "str");
$object_id    = CView::get("object_id", "num pos");
$patient_id   = CView::get("patient_id", "num pos");
$praticien_id = CView::get("praticien_id", "num pos");

CView::checkin();

$object = new $object_class;
$object->load($object_id);

$object->countDocItems();

$user = CMediusers::get();

// Praticien concerné
if (!$user->isPraticien() && $praticien_id) {
  $user = new CMediusers();
  $user->load($praticien_id);
}

$user->loadRefFunction();
$user->_ref_function->loadRefGroup();
$user->canDo();

$compte_rendu = new CCompteRendu();

$smarty = new CSmartyDP();

$smarty->assign("object", $object);
$smarty->assign("praticien", $user);
$smarty->assign("patient_id", $patient_id);

$smarty->display("inc_widget_count_documents.tpl");