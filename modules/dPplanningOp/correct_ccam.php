<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;

CView::checkin();

foreach (array("COperation", "CSejour", "CConsultation") as $_object_class) {
  $request = new CRequest();
  $request->addSelect("object_id");
  $request->addTable("user_log");
  $request->addWhere(
    array(
      "date" => ">= '2018-09-01 00:00:00'",
      "object_class" => "= '$_object_class'",
      "fields" => "= 'codes_ccam'",
      "type"   => "= 'store'"
    )
  );
  $request->addGroup("object_id");

  $ds = CSQLDataSource::get("std");

  $object_ids = $ds->loadColumn($request->makeSelect());
  /** @var CMbObject $object */
  $object = new $_object_class;
  $spec = $object->getSpec();
  $where = array(
    "codes_ccam" => "IS NULL",
    $object->_spec->key => CSQLDataSource::prepareIn($object_ids),
    "(SELECT COUNT(*) FROM acte_ccam WHERE object_class = '$_object_class' AND object_id = {$spec->key}) != 0"
  );

  $objects = $object->loadList($where);

  CStoredObject::massLoadBackRefs($objects, "user_logs", "date DESC");

  CAppUI::stepAjax(count($objects) . " $_object_class trouvés");

  /** @var CStoredObject $_object */
  foreach ($objects as $_object) {
    $_object->loadLogs();

    foreach ($_object->_ref_logs as $_log) {
      if (preg_match("/codes_ccam/", $_log->fields)) {
        $json = json_decode($_log->extra);
        $_object->codes_ccam = $json->codes_ccam;
        $_object->updateCCAMFormField();
        $_object->updateCCAMPlainField();
        if ($msg = $_object->store()) {
          CAppUI::stepAjax("Erreur de sauvegarde pour $_object->_guid : $msg");
        }
        break;
      }
    }
  }
}