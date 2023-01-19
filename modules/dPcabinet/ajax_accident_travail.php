<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CAccidentTravail;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();
$consult_id   = CView::getRefCheckEdit('consult_id', 'ref class|CConsultation');
$sejour_id    = CView::get('sejour_id', 'ref class|CSejour');
$object_class = CView::get('object_class', 'enum list|CConsultation|CSejour');
CView::checkin();

/** @var CConsultation|CSejour $object */
$object = new $object_class();
$object->load($consult_id ?: $sejour_id);

CAccessMedicalData::logAccess($object);

/** @var CAccidentTravail $accident_travail */
$accident_travail = $object->loadUniqueBackRef('accident_travail');

if (!$accident_travail->_id) {
  $accident_travail->object_class = $object->_class;
  $accident_travail->object_id    = $object->_id;
}

$smarty = new CSmartyDP();
$smarty->assign('accident_travail', $accident_travail);
$smarty->assign('consult_id'      , $consult_id);
$smarty->assign('sejour_id'       , $sejour_id);
$smarty->assign('object_class'    , $object_class);
$smarty->display('inc_type_assurance_reglement/accident_travail');
