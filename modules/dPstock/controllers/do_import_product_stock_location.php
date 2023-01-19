<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CApp;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Stock\CProductStockLocation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Core\CCanDo;
use Ox\Core\CView;

CCanDo::checkEdit();
CApp::setTimeLimit(300);
CApp::setMemoryLimit('2048M');

$pending_service = CView::post('psl_s', 'str');
$pending_bloc    = CView::post('psl_b', 'str');

CView::checkin();
$processed_lines = 0;
$group_id        = CGroups::loadCurrent()->_id;
$ds              = CSQLDataSource::get("std");
if ($pending_service !== null) {
  foreach ($pending_service as $_rowserv) {
    $psl               = new CProductStockLocation();
    // On utilise pas loadMatchingObjectEsc car le updatePlainFields calcule la position
    $where             = [
        "name"          => $ds->prepare("= ?", $_rowserv['name']),
        "object_class"  => $ds->prepare("= ?", 'CService'),
        "object_id"     => $ds->prepare("= ?", $_rowserv['object_id'])
    ];
    $psl->loadObject($where);
    $psl->name         = $_rowserv['name'];
    $psl->object_class = 'CService';
    $psl->object_id    = $_rowserv['object_id'];
    $psl->group_id     = $group_id;
    $psl->desc         = $_rowserv['desc'] !== "" ? $_rowserv['desc'] : null;
    $psl->position     = $_rowserv['position'];
    $psl->actif        = $_rowserv['actif'];
    if ($msg = $psl->store()) {
      CAppUI::stepAjax($msg, UI_MSG_WARNING);
      continue;
    }
    $processed_lines++;
  }
}
if ($pending_bloc !== null) {
  foreach ($pending_bloc as $_rowbloc) {
    $psl               = new CProductStockLocation();
    // On utilise pas loadMatchingObjectEsc car le updatePlainFields calcule la position
    $where             = [
        "name"          => $ds->prepare("= ?", $_rowbloc['name']),
        "object_class"  => $ds->prepare("= ?", 'CBlocOperatoire'),
        "object_id"     => $ds->prepare("= ?", $_rowbloc['object_id'])
    ];
    $psl->loadObject($where);
    $psl->name         = $_rowbloc['name'];
    $psl->object_class = 'CBlocOperatoire';
    $psl->object_id    = $_rowbloc['object_id'];
    $psl->group_id     = $group_id;
    $psl->desc         = $_rowbloc['desc'] !== "" ? $_rowbloc['desc'] : null;
    $psl->position     = $_rowbloc['position'];
    $psl->actif        = $_rowbloc['actif'];
    if ($msg = $psl->store()) {
      CAppUI::stepAjax($msg, UI_MSG_WARNING);
      continue;
    }
    $processed_lines++;
  }
}
$msg = CAppUI::tr(
  "CProductStockLocation-import-count_element_imported",
  [
    "var1" => $processed_lines
  ]
);
CAppUI::stepAjax($msg, UI_MSG_OK);
