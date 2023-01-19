<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\Forms\CExClassEvent;

CCanDo::checkRead();

$object_class = CView::get('object_class', 'str');
$object_id    = CView::get('object_id', 'ref class|CMbObject meta|object_class');

CView::checkin();

if (!$object_class || !$object_id) {
  CAppUI::commonError();
}

CView::enableSlave();

/** @var CSejour|CPatient|CConsultation $object */
$object = new $object_class();
$object->load($object_id);

$ex_events = CExClassEvent::loadMandatoryEvents($object);

$object->loadRelPatient();

$smarty = new CSmartyDP();
$smarty->assign('object', $object);
$smarty->assign('ex_events', $ex_events);
$smarty->display('inc_vw_mandatory_ex_objects.tpl');
