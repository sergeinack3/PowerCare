<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\ObservationResult\CObservationResultSet;

CCanDo::checkAdmin();

$object_class = CValue::getOrSession("object_class");
$object_id    = CValue::getOrSession("object_id");

$ors = new CObservationResultSet();
$ors->context_class = $object_class;
$ors->context_id    = $object_id;

/** @var CObservationResultSet[] $result_sets */
$result_sets = $ors->loadMatchingList();

foreach ($result_sets as $_result_set) {
  $_result_set->loadRefPatient();
  $_result_set->loadRefContext()->loadComplete();
  $_result_set->loadLastLog()->loadRefUser();
  $_results = $_result_set->loadRefsResults();

  foreach ($_results as $_result) {
      $_result->loadRefValues();
  }
}

$smarty = new CSmartyDP();
$smarty->assign("result_sets", $result_sets);
$smarty->display("inc_list_observation_results.tpl");
