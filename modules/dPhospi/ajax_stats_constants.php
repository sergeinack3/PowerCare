<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$date_min    = CView::get('date_min', array('date', 'default' => CMbDT::date('-1 MONTH')));
$date_max    = CView::get('date_max', array('date', 'default' => CMbDT::date()));
$sejour_type = CView::get('sejour_type', 'enum list|' . implode('|', CSejour::$types));
$field       = CView::get('constant', 'enum list|' . implode('|', array_keys(CConstantesMedicales::$list_constantes)) . ' notNull');
$operator    = CView::get('operator', 'enum list|=|<=|<|>|>= notNull');
$value       = CView::get('value', 'float notNull');
$export      = CView::get('export', 'bool default|0');

CView::checkin();

$constant = new CConstantesMedicales();

$where = array(
  'context_class' => "= 'CSejour'",
  'datetime'      => "BETWEEN '$date_min 00:00:00' AND '$date_max 23:59:59'"
);

$ljoin = array();

$params = CConstantesMedicales::$list_constantes;
if (array_key_exists('formfields', $params[$field]) && count($params[$field]['formfields']) == 2) {
  $where[] = "CAST(SUBSTR($field, 1, LOCATE('|', $field) - 1) AS DOUBLE) $operator $value "
    . "OR CAST(SUBSTR($field, LOCATE('|', $field) + 1) AS DOUBLE) $operator $value";
}
else {
  $where[$field] = "$operator $value";
}

if ($sejour_type) {
  $ljoin['sejour'] = "sejour_id = context_id";
  $where['type']   = "= '$sejour_type'";
}

/** @var CConstantesMedicales[] $constants */
$constants = $constant->loadList($where, "$field DESC", null, 'constantes_medicales_id', $ljoin);

CMbObject::massLoadFwdRef($constants, 'patient_id');
$sejours      = CMbObject::massLoadFwdRef($constants, 'context_id', 'CSejour');
$affectations = CMbObject::massLoadBackRefs($sejours, 'affectations');
CMbObject::massLoadFwdRef($affectations, 'service_id');

foreach ($constants as $constant) {
  $constant->loadRefPatient();
  $constant->loadRefContext();
  /** @var CSejour $sejour */
  $sejour      = $constant->_ref_context;
  $affectation = $sejour->loadRefCurrAffectation($constant->datetime);
  $affectation->loadRefService();
}

/* We reset the variable because unit is set from the configurations in CConstantesMedicales::updateFormFields function */
$params = CConstantesMedicales::$list_constantes;

if ($export) {
  $file = new CCSVFile();

  $file->writeLine(
    array(
      CAppUI::tr('CPatient'),
      CAppUI::tr('CSejour'),
      CAppUI::tr('CService'),
      CAppUI::tr('CConstantesMedicales-datetime'),
      CAppUI::tr("CConstantesMedicales-$field") . " ({$params[$field]['unit']})",
    )
  );

  foreach ($constants as $constant) {
    $data = array(
      $constant->_ref_patient->_view,
      $constant->_ref_context,
      $constant->_ref_context->_ref_curr_affectation->_ref_service->_view,
      CMbDT::format($constant->datetime, CAppUI::conf('datetime'))
    );

    if (array_key_exists('formfields', $params[$field])) {
      $data[4] = '';
      $i       = 1;
      foreach ($params[$field]['formfields'] as $formfield) {
        $data[4] .= $constant->$formfield;
        if ($i < count($params[$field]['formfields'])) {
          $data[4] .= '/';
        }
        $i++;
      }
    }
    else {
      $data[4] = $constant->$field;
    }

    $file->writeLine($data);
  }

  $file->stream('Export_' . str_replace(' ', '_', CAppUI::tr("CConstantesMedicales-$field")));
  CApp::rip();
}

$smarty = new CSmartyDP();
$smarty->assign('constants', $constants);
$smarty->assign('field', $field);
$smarty->assign('params', CConstantesMedicales::$list_constantes);
$smarty->display('inc_stats_constants.tpl');