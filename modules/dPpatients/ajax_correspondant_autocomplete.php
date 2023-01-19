<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CCorrespondantPatient;

CCanDo::checkRead();

$patient_id = CValue::get("patient_id");
$type       = CValue::get("type");
$nom        = @$_POST["$type"];

CView::enableSlave();

$corresp             = new CCorrespondantPatient();
$where               = array();
$where[]             = "`nom` LIKE '%$nom%' OR `surnom` LIKE '%$nom%'";
$where["patient_id"] = " = '$patient_id'";
$correspondants      = $corresp->loadList($where, "nom");

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("nom", $nom);
$smarty->assign("correspondants", $correspondants);

$smarty->display("ajax_correspondant_autocomplete.tpl");