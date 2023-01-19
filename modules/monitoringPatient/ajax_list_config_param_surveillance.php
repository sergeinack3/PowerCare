<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\ObservationResult\CObservationValueCodingSystem;

CCanDo::checkAdmin();

$object_class = CView::get("object_class", "str class|CObservationValueCodingSystem notNull");
$start        = (int)CView::get("start", "num");

CView::checkin();

$step = 30;

$group_id = CGroups::loadCurrent()->_id;

$where = array(
  "group_id IS NULL OR group_id = '$group_id'"
);

/** @var CObservationValueCodingSystem $param */
$param        = new $object_class();
$params       = $param->loadList($where, "coding_system, code", "$start,$step");
$params_total = $param->countList($where);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("params"      , $params);
$smarty->assign("params_total", $params_total);
$smarty->assign("object_class", $object_class);
$smarty->assign("start"       , $start);
$smarty->display("inc_list_config_param_surveillance");
