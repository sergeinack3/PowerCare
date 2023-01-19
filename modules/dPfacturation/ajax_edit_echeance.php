<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Facturation\CEcheance;

CCanDo::checkEdit();
$echeance_id   = CValue::get("echeance_id");
$facture_id    = CValue::getOrSession("facture_id");
$facture_class = CValue::getOrSession("facture_class");

$echeance = new CEcheance();
$echeance->load($echeance_id);

if (!$echeance->_id) {
  $echeance->object_id    = $facture_id;
  $echeance->object_class = $facture_class;
}

// Creation du template
$smarty = new CSmartyDP();

$smarty->assign("echeance" , $echeance);

$smarty->display("vw_edit_echeance");