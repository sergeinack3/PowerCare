<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Prescription\CElementPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;

CCanDo::checkRead();

$category_id             = CValue::get("category_id");
$element_prescription_id = CValue::get("element_prescription_id");
$prescription_id         = CValue::get("prescription_id");

// Chargement de la ligne selectionnée
$element = new CElementPrescription();
$element->load($element_prescription_id);

// Chargement de toutes les lignes de la categorie
$line                                                   = new CPrescriptionLineElement();
$ljoin["element_prescription"]                          = "prescription_line_element.element_prescription_id = element_prescription.element_prescription_id";
$where["prescription_id"]                               = " = '$prescription_id'";
$where["element_prescription.category_prescription_id"] = " = '$category_id'";
$lines                                                  = $line->loadList($where, null, null, null, $ljoin);

$warning = false;
/*
foreach($lines as $_line){
  if ($element_prescription_id == $_line->element_prescription_id){
    $warning = true;
  }
}
*/
$last_line = end($lines);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("warning", $warning);
$smarty->assign("element", $element);
$smarty->assign("lines", $lines);
$smarty->assign("last_line", $last_line);
$smarty->assign("nodebug", true);
$smarty->assign("current_date", CMbDT::date());
$smarty->display("inc_vw_modal");
