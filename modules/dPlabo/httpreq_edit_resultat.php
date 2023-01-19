<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Labo\CPrescriptionLabo;
use Ox\Mediboard\Labo\CPrescriptionLaboExamen;

CCanDo::checkRead();
$user = CUser::get();

$typeListe = CValue::getOrSession("typeListe");

// Chargement de l'item choisi
$prescriptionItem = new CPrescriptionLaboExamen;
$prescriptionItem->load(CValue::getOrSession("prescription_labo_examen_id"));

if ($prescriptionItem->_id) {
  $prescriptionItem->date = CMbDT::date();
}

$siblingItems = array();
if ($prescriptionItem->loadRefs()) {

  $siblingItems = $prescriptionItem->loadSiblings();
  $prescriptionItem->_ref_prescription_labo->loadRefs();
  $prescriptionItem->_ref_examen_labo->loadRefsFwd();
  $prescriptionItem->_ref_examen_labo->loadExternal();
  if ($prescriptionItem->_ref_prescription_labo->_status >= CPrescriptionLabo::VALIDEE) {
    $prescriptionItem->_locked = 1;
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("prescriptionItem", $prescriptionItem);
$smarty->assign("siblingItems", $siblingItems);
$smarty->assign("user_id", $user->_id);

$smarty->display("inc_edit_resultat");
