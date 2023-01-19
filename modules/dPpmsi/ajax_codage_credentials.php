<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\Ccam\CCodageCCAM;

CCanDo::checkEdit();

$object_class = CView::get('object_class', 'enum list|COperation');
$object_id    = CView::get('object_id', 'ref meta|object_class');
$reload       = CView::get('reload', 'bool default|0');

CView::checkin();

/** @var CCodable $object */
$object = CMbObject::loadFromGuid("{$object_class}-{$object_id}");

CAccessMedicalData::logAccess($object);

$object->isCoded();
$object->loadRefsCodagesCCAM();

$codages = array();
foreach ($object->_ref_codages_ccam as $user_id => $_codages) {
  if ($object_class == 'CSejour') {
    $_codages = reset($_codages);
  }

  /** @var CCodageCCAM $_codage */
  $_codage = reset($_codages);
  $_codage->loadPraticien();
  $_codage->_ref_praticien->loadRefFunction();
  $codages[] = $_codage;
}

$smarty = new CSmartyDP();
$smarty->assign('object', $object);
$smarty->assign('codages', $codages);
$smarty->assign('reload', $reload);
$smarty->display('inc_codage_credentials');