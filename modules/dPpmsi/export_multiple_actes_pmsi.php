<?php 
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkAdmin();

$object_guids = json_decode(stripslashes(CView::post('object_guids', 'str default|[]')));

CView::checkin();

$count_operations = 0;
$count_sejours = 0;
$errors_operations = 0;
$errors_sejours = 0;

foreach ($object_guids as $object_guid) {
  /** @var CCodable $object */
  $object = CMbObject::loadFromGuid($object_guid);

  switch ($object->_class) {
    case 'COperation':
      /** @var COperation $operation */
      $operation = $object;
      $operation->loadRefsFwd();
      $codes = explode("|", $operation->codes_ccam);
      $acts = CMbArray::pluck($operation->_ref_actes_ccam, 'code_acte');

      foreach ($operation->_ref_actes_ccam as $act) {
        $act->loadRefsFwd();
      }

      // Suppression des actes non codés
      if (CAppUI::gconf("dPsalleOp CActeCCAM del_acts_not_rated")) {
        foreach ($codes as $_key => $_code) {
          $key = array_search($_code, $actes);
          if ($key === false) {
            unset($codes[$_key]);
          }
        }
      }
      $object->_codes_ccam = $codes;

      $sejour = $operation->_ref_sejour;
      $sejour->loadRefsFwd();
      $sejour->loadNDA();
      $sejour->_ref_patient->loadIPP();
      break;
    case 'CSejour':
      /** @var CSejour $sejour */
      $sejour = $object;

      $sejour->loadRefsFwd();
      $sejour->loadNDA();
      $sejour->_ref_patient->loadIPP();
      break;
    default:
  }

  $is_facture = $object->facture;
  /* We must modify a field to force sent the sejour */
  $object->facture = $is_facture ? '0' : '1';
  $object->_force_sent = true;
  $object->loadLastLog();

  try {
    $object->store();

    /* We set back the field to its previous value */
    $object->facture = $is_facture;
    $object->_no_synchro_eai = true;
    $object->store(false);

    switch ($object->_class) {
      case 'COperation':
        $count_operations++;
        break;
      case 'CSejour':
        $count_sejours++;
        break;
      default:
    }
  }
  catch (CMbException $e) {
    switch ($object->_class) {
      case 'COperation':
        $errors_operations++;
        break;
      case 'CSejour':
        $errors_sejours++;
        break;
      default:
    }
  }
}

if ($count_operations) {
  CAppUI::setMsg("$count_operations interventions exportées", UI_MSG_OK);
}
if ($count_sejours) {
  CAppUI::setMsg("$count_sejours séjours exportés", UI_MSG_OK);
}
if ($errors_operations) {
  CAppUI::setMsg("$errors_operations interventions en erreur", UI_MSG_ERROR);
}
if ($errors_sejours) {
  CAppUI::setMsg("$errors_sejours séjours en erreur", UI_MSG_ERROR);
}

echo CAppUI::getMsg();