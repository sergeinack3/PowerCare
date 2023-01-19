<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\Ccam\CDatedCodeCCAM;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Vérification d'activités 4 sur les actes pour la suppression d'actes non cotés avant envoi
 */
CCanDo::checkEdit();

$object_class = CView::get("object_class", 'str');
$object_id    = CView::get("object_id", 'ref meta|object_class');

CView::checkin();

/** @var CCodable $object */
$object = new $object_class;
$object->load($object_id);

if ($object instanceof CSejour || $object instanceof COperation) {
  CAccessMedicalData::logAccess($object);
}

$actes = explode("|", $object->codes_ccam);
$object->loadRefsActesCCAM();

$activites = CMbArray::pluck($object->_ref_actes_ccam, "code_activite");

$activite_1 = array_search("1", $activites);
$activite_4 = array_search("4", $activites);

$completed_activite_1 = 1;
$completed_activite_4 = 1;

foreach ($actes as $_acte) {
  $acte = CDatedCodeCCAM::get($_acte);

  if (isset($acte->activites["1"]) && $activite_1 === false) {
    $completed_activite_1 = 0;
  }
  if (isset($acte->activites["4"]) && $activite_4 === false) {
    $completed_activite_4 = 0;
    break;
  }
}

$response = array(
  "activite_1" => $completed_activite_1,
  "activite_4" => $completed_activite_4,
);

echo json_encode($response);