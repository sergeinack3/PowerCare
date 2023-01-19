<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Mediboard\CompteRendu\CAideSaisie;

/**
 * Export CSV des aides à la saisie
 */
CCanDo::checkRead();

$list         = CValue::post('id', array());
$owner        = CValue::post('owner');
$object_class = CValue::post('object_class');

CMbObject::$useObjectCache = false;

if (!is_array($list)) {
  $list = explode("-", $list);
}

$filename = 'Aides saisie'. ($owner ? " - $owner" : '') . ($object_class ? " - ".CAppUI::tr($object_class) : '') . '.csv';

$out = fopen("php://output", "w");
header("Content-Type: application/csv");
header("Content-Disposition: attachment; filename=\"$filename\"");

$aide = new CAideSaisie();

$first_line = array_keys($aide->getCSVFields());
$first_line[] = "chapitre";

fputcsv($out, $first_line);

foreach ($list as $id) {
  if (!$aide->load($id)) {
    continue;
  }

  $fields = $aide->getCSVFields();

  switch ($aide->class) {
    case "CTransmissionMedicale":
      if ($aide->depend_value_2) {
        $fields["depend_value_2"] = $aide->_vw_depend_field_2;
        $fields["chapitre"] = $aide->_ref_object_dp_2->chapitre;
      }
      break;
    default:
  }

  fputcsv($out, $fields);
}
