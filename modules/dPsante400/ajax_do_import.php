<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\Sante400\CMouvFactory;
use Ox\Mediboard\Sante400\CRecordSante400;

CCanDo::checkAdmin();

$type   = CValue::get("type");
$offset = CValue::get("offset");
$step   = CValue::get("step");

CRecordSante400::$verbose = CValue::get("verbose");

if (!$type) {
  CAppUI::stepAjax("CMouvement400-error-no_type", UI_MSG_ERROR);
}

CAppUI::stepAjax("CMouvement400-alert-import", UI_MSG_ALERT, $type, $offset, $step);

$mouv = CMouvFactory::create($type);

if (!$mouv->origin || !$mouv->origin_key_field) {
  CAppUI::stepAjax("CMouvement400-error-trigger_no_origin", UI_MSG_WARNING, $mouv->class);

  return;
}

$query  = "SELECT * FROM $mouv->base.$mouv->origin 
  WHERE $mouv->origin_key_field >= ?
  AND $mouv->origin_key_field < ?";
$values = array(
  $offset,
  $offset + $step,
);

$mouvs  = CRecordSante400::loadMultiple($query, $values, $step, $mouv->class);
$totals = array(
  "success" => 0,
  "failure" => 0,
);

$failures = array();

foreach ($mouvs as $_mouv) {
  $_mouv->value_prefix = $_mouv->origin_prefix;
  $origin_key          = $_mouv->data[$mouv->origin_key_field];

  // Initialize trigger field
  $data                            = array();
  $data[$_mouv->trigger_key_field] = null;
  $data[$_mouv->type_field]        = null;
  $data[$_mouv->when_field]        = null;

  // Rebuild triggers fields
  foreach ($_mouv->data as $_field => $_value) {
    $data[$_mouv->old_prefix . $_field] = null;
    $data[$_mouv->new_prefix . $_field] = $_value;
  }

  // Proceed trigger
  $_mouv->data = $data;
  $_mouv->initialize();
  $done = $_mouv->proceed(false);

  // Collect errors
  $totals[$done ? "success" : "failure"]++;
  if (!$done) {
    CAppUI::stepAjax("CMouvement400-alert-failed-trigger", UI_MSG_WARNING, $origin_key);
  }
}

if ($totals["success"]) {
  CAppUI::stepAjax("CMouvement400-report-success-count", UI_MSG_OK, $totals["success"]);
}

if ($totals["failure"]) {
  CAppUI::stepAjax("CMouvement400-report-failure-count", UI_MSG_WARNING, $totals["failure"]);
}