<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

$patient_id      = CView::get('patient_id', "ref class|CPatient");
$sejour_id       = CView::get('sejour_id', 'ref class|CSejour');
$constante_names = CView::get('constante_names', "str");
CView::checkin();

if (!$constante_names) {
  return;
}

$constante_names = explode("-", $constante_names);

$patient = new CPatient();
$patient->load($patient_id);

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

if ($patient->_id) {
  $constante_names_to_load = array_filter(
    $constante_names,
    function ($v) {
      return $v[0] !== '_';
    }
  );

  $constantes       = $patient->loadRefLatestConstantes(null, $constante_names_to_load);
  $constante_values = array_fill_keys($constante_names, '&mdash;');

  foreach ($constante_names as $_name) {
    if ($constantes[0]->$_name) {
      $constante_val = $constantes[0]->$_name;
      if (isset(CConstantesMedicales::$list_constantes[$_name]['formfields'])) {
        $field_name = CConstantesMedicales::$list_constantes[$_name]['formfields'][0];
        $constante_val = $constantes[0]->$field_name;
      }

      $constante_values[$_name] = sprintf(
        "%s %s",
        $constante_val,
        CConstantesMedicales::$list_constantes[$_name]["unit"]
      );
    }

    if ($_name === "poids") {
      $date  = CMbDT::format($constantes[1]['poids'], CAppUI::conf('datetime'));
      $poids = sprintf(
        "<span title='%s'>%s %s</span>",
        $date,
        $constantes[0]->$_name,
        CConstantesMedicales::$list_constantes[$_name]["unit"]
      );

      if ($sejour && $sejour->_id && array_key_exists('poids', $constantes[1]) && array_key_exists('poids', $constantes[2])) {
        if ($constantes[2]['poids'] != $sejour->_guid) {
          $object = CMbObject::loadFromGuid($constantes[2]['poids']);
          if (!$object->_id || !in_array($object->_class, ['CConsultation', 'COperation']) || $object->sejour_id != $sejour->_id) {
            // Weight outdated
            $msg  = CAppUI::tr('CPatient-msg-Entry is outdated');
            $date = CMbDT::format($constantes[1]['poids'], CAppUI::conf('datetime'));

            $constante_val = $constantes[0]->$_name;
            if (isset(CConstantesMedicales::$list_constantes[$_name]['formfields'])) {
              $field_name = CConstantesMedicales::$list_constantes[$_name]['formfields'][0];
              $constante_val = $constantes[0]->$field_name;
            }

            $poids = sprintf(
              "<span title='%s : %s' style='color: firebrick;'><strong>%s %s</strong></span>",
              $msg,
              $date,
              $constante_val,
              CConstantesMedicales::$list_constantes[$_name]["unit"]
            );
          }
        }
      }

      $constante_values[$_name] = $poids;
    }
  }

  CApp::json($constante_values);
}

CApp::rip();