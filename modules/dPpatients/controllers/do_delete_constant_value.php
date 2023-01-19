<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Patients\Constants\CAbstractConstant;
use Ox\Mediboard\Patients\Constants\CConstantException;

CCanDo::checkAdmin();
$value_guid      = CView::get("value_guid", "str");
CView::checkin();

$explode     = explode("-", $value_guid);
$value_id    = $explode[1];
$value_class = $explode[0];

/** @var CAbstractConstant $value */
$value = new $value_class;
$value->load($value_id);
try {
  $value->storeInactive();
} catch (CConstantException $constantException) {
}
