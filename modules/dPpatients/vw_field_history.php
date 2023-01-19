<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;

CCanDo::checkAdmin();

$object_class = CView::get('object_class', 'str notNull');
$object_id    = CView::get('object_id', 'ref class|CStoredObject meta|object_class');
$field_name   = CView::get('field_name', 'str notNull');

CView::checkin();

if (!$object_class || !class_exists($object_class)) {
    CAppUI::commonError("$object_class.none");
}

/** @var CStoredObject $obj */
$obj = new $object_class();
$obj->load($object_id);

if (!$obj || !$obj->_id) {
  CAppUI::commonError("$object_class.none");
}

$history = $obj->getFieldHistory($field_name);

$smarty = new CSmartyDP();
$smarty->assign('history', $history);
$smarty->assign('field_name', $field_name);
$smarty->assign('object_class', $object_class);
$smarty->display('vw_field_history.tpl');