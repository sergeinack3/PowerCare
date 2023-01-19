<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusersStats;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkAdmin();
$type                   = CView::get("type", "enum list|RDV|consult|FSE default|RDV");
$date                   = CView::get("date", "date");
$period                 = CView::get("period", "enum list|day|week|month|year default|month");
$consult_no_sejour      = CView::get("consult_no_sejour", "bool", "default|1");
$consult_sejour_consult = CView::get("consult_sejour_consult", "bool", "default|1");
$consult_sejour_urg     = CView::get("consult_sejour_urg", "bool", "default|0");
$consult_sejour_ext     = CView::get("consult_sejour_ext", "bool", "default|0");
$consult_sejour_autre   = CView::get("consult_sejour_autre", "bool", "default|0");
CView::checkin();

$stats = new CMediusersStats($date, $period, "date", 18);

$consult = new CConsultation();
$group = CGroups::loadCurrent();
$ds = $consult->_spec->ds;

$query_complement = "1";
if ($type == "consult") {
  $query_complement = "consultation.chrono > 32
     OR consultation.traitement       IS NOT NULL
     OR consultation.histoire_maladie IS NOT NULL
     OR consultation.conclusion       IS NOT NULL
     OR consultation.examen           IS NOT NULL
  ";
}

$query = "SELECT COUNT(*) total, user_id, $stats->sql_date AS refdate
  FROM `consultation`
  LEFT JOIN plageconsult AS plage ON plage.plageconsult_id = consultation.plageconsult_id
  LEFT JOIN users_mediboard AS user ON user.user_id = plage.chir_id
  LEFT JOIN functions_mediboard AS function ON function.function_id = user.function_id
  LEFT JOIN sejour ON sejour.sejour_id = consultation.sejour_id
  WHERE $stats->sql_date BETWEEN '$stats->min_date' AND '$stats->max_date'
  AND function.group_id = '$group->_id'
  AND consultation.annule != '1'
  AND consultation.patient_id IS NOT NULL
  AND ($query_complement)";

if ($consult_no_sejour && !$consult_sejour_consult) {
  $query .= "\nAND consultation.sejour_id IS NULL";
}

if ($consult_sejour_consult && !$consult_no_sejour) {
  $query .= "\nAND consultation.sejour_id IS NOT NULL";
}

if ($consult_sejour_consult && $consult_no_sejour) {
  $query .= "\nAND (consultation.sejour_id IS NULL OR consultation.sejour_id IS NOT NULL)";
}

if ($consult_sejour_urg) {
  $query .= "\nAND sejour.type " . CSQLDataSource::prepareIn(CSejour::getTypesSejoursUrgence());
}

if ($consult_sejour_ext) {
  $query .= "\nAND sejour.type = 'exte'";
}

if ($consult_sejour_autre) {
  $query .= "\nAND sejour.type " . CSQLDataSource::prepareNotIn(array_merge(CSejour::getTypesSejoursUrgence(), ['exte']));
}

$query .= "\nGROUP BY user_id, refdate\nORDER BY refdate DESC";


foreach ($result = $ds->loadList($query) as $_row) {
  $stats->addTotal($_row["user_id"], $_row["refdate"], $_row["total"]);
}

$stats->display("CMediusersStats-CConsultation-$type");
