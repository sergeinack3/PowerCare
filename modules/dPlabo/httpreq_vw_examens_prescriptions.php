<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Labo\CPrescriptionLabo;

CCanDo::checkRead();

$prescription_labo_id = CValue::getOrSession("prescription_labo_id");

// Tableau permettant de stocker la liste des presriptions
$tab_prescription = array();
$tab_pack_prescription = array();
// Chargement de la prescription demandée
$prescription = new CPrescriptionLabo();
$prescription->load($prescription_labo_id);
$prescription->loadRefs();
if ($prescription->_id) {
  foreach ($prescription->_ref_examens as $curr_examen) {
    $curr_examen->getSiblings();
  }
}

if ($prescription->_id) {
  // Tableau de classement des analyses par pack
  foreach ($prescription->_ref_prescription_items as $key => $item) {
    if ($item->_ref_pack->_id) {
      // Prescriptions appartenant a un pack
      $tab_pack_prescription[$item->_ref_pack->_id][] = $item;
    }
    else {
      // Autres prescriptions
      $tab_prescription[] = $item;
    }
  }
}

// Création du template
$smarty = new CSmartyDP();
 
$smarty->assign("prescription", $prescription);
$smarty->assign("tab_pack_prescription", $tab_pack_prescription);
$smarty->assign("tab_prescription", $tab_prescription);
$smarty->display("inc_vw_examens_prescriptions");
