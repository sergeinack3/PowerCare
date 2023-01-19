<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Hospi\CMovement;

/**
 * Movements
 */
CCanDo::checkRead();

$page          = CView::get('page', "num default|0");
$movement_type = CView::get("movement_type", "str");
$sejour_id     = CView::get("sejour_id", "ref class|CSejour");
$spec_date_min = array(
  "dateTime",
  "default" => CMbDT::dateTime("-7 day")
);
$_date_min     = CView::get("_date_min", $spec_date_min);
$spec_date_max = array(
  "dateTime",
  "default" => CMbDT::dateTime("+1 day")
);
$_date_max     = CView::get("_date_max", $spec_date_max);

CView::checkin();

CAccessMedicalData::logAccess("CSejour-$sejour_id");

// Chargement du filtre
$movement                = new CMovement();
$movement->sejour_id     = $sejour_id;
$movement->movement_type = $movement_type;
$movement->_date_min     = $_date_min;
$movement->_date_max     = $_date_max;
$movement->nullifyEmptyFields();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("page"         , $page);
$smarty->assign("movement_type", $movement_type);
$smarty->assign("movement"     , $movement);
$smarty->display("vw_movements.tpl");