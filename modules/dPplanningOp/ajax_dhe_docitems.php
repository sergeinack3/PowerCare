<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$object_class = CView::get("object_class", "enum list|CSejour|CConsultation|COperation");
$object_id    = CView::get("object_id", "ref class|$object_class");

CView::checkin();

/** @var CSejour|CConsultation|COperation $object */
$object = new $object_class;
$object->load($object_id);

CAccessMedicalData::logAccess($object);

$object->loadRefsDocItems();

$smarty = new CSmartyDP();

$smarty->assign("object", $object);

$smarty->display("dhe/inc_sum_documents");