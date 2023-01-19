<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Prescription\CElementPrescription;

CCanDo::checkRead();

$libelle = CValue::post("libelle");

CView::enableSlave();

// Recuperation de la fonction de l'utilisateur courant
$function_id = CMediusers::get()->function_id;

// Recherche des elements que l'utilisateur courant a le droit de prescrire
// (executant de la categorie et categorie prescritible par executant)
$ljoin                                   = array();
$ljoin["category_prescription"]          =
  "category_prescription.category_prescription_id = element_prescription.category_prescription_id";
$ljoin["function_category_prescription"] =
  "function_category_prescription.category_prescription_id = category_prescription.category_prescription_id";

$where                                                                     = array();
$where["element_prescription.libelle"]                                     = " LIKE '%$libelle%'";
$where["element_prescription.cancelled"]                                   = "= '0'";
$where["category_prescription.inactive"]                                   = "= '0'";
$where["category_prescription.prescription_executant"]                     = " = '1'";
$where["function_category_prescription.function_category_prescription_id"] = " IS NOT NULL";
$where["function_category_prescription.function_id"]                       = " = '$function_id'";

$element = new CElementPrescription();
/** @var CElementPrescription[] $elements */
$elements = $element->loadList($where, null, null, null, $ljoin);
CStoredObject::massLoadFwdRef($elements, "category_prescription_id");

// Chargement de la categorie des elements
foreach ($elements as $_element) {
  $_element->loadRefCategory();
}

// Création du template
$smarty = new CSmartyDP("modules/dPprescription");

$smarty->assign("elements", $elements);
$smarty->assign("libelle", $libelle);
$smarty->assign("category_id", "");
$smarty->assign("nodebug", true);

$smarty->display("httpreq_do_element_autocomplete");
