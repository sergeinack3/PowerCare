<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CRequest;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusersStats;

CCanDo::checkAdmin();

$type   = CValue::get("type", "CEvenementSSR");
$date   = CValue::get("date");
$period = CValue::get("period", "month");

$stats = new CMediusersStats($date, $period, "DATE(debut)", 18);

$consult = new CConsultation();
$ds      = $consult->_spec->ds;

$group = CGroups::loadCurrent();

$query = new CRequest();
$query->addColumn("COUNT(*) total");
$query->addColumn("therapeute_id", "user_id");
$query->addColumn($stats->sql_date, "refdate");
$query->addWhere("$stats->sql_date BETWEEN '$stats->min_date' AND '$stats->max_date'");
$query->addWhereClause("functions_mediboard.group_id", "= '$group->_id'");
$query->addGroup("therapeute_id, refdate");
$query->addOrder("refdate DESC");

$totals = array();

switch ($type) {
  case "CEvenementSSR":
    $query->addTable("`evenement_ssr`");
    $query->addLJoinClause("sejour", "sejour.sejour_id = evenement_ssr.sejour_id");
    $query->addLJoinClause("users_mediboard", "users_mediboard.user_id = evenement_ssr.therapeute_id");
    $query->addLJoinClause("functions_mediboard", "functions_mediboard.function_id = users_mediboard.function_id");

    // Réalisés
    $query1 = clone($query);
    $query1->addWhereClause("evenement_ssr.realise", "= '1'");
    foreach ($ds->loadList($query1->makeSelect()) as $_row) {
      $stats->addTotal($_row["user_id"], $_row["refdate"], $_row["total"], "realises");
    }

    // Annulés
    $query1 = clone($query);
    $query1->addWhereClause("evenement_ssr.annule", "= '1'");
    foreach ($ds->loadList($query1->makeSelect()) as $_row) {
      $stats->addTotal($_row["user_id"], $_row["refdate"], $_row["total"], "annules");
    }

    // Ni réalisés ni annulés
    $query1 = clone($query);
    $query1->addWhereClause("evenement_ssr.annule", "= '0'");
    $query1->addWhereClause("evenement_ssr.realise", "= '0'");
    foreach ($ds->loadList($query1->makeSelect()) as $_row) {
      $stats->addTotal($_row["user_id"], $_row["refdate"], $_row["total"], "planifies");
    }

    break;

  case "CActeSSR":
    // CsARR
    $query1 = clone($query);
    $query1->addTable("acte_csarr");
    $query1->addLJoinClause("evenement_ssr", "acte_csarr.evenement_ssr_id = evenement_ssr.evenement_ssr_id");
    $query1->addLJoinClause("sejour", "sejour.sejour_id = evenement_ssr.sejour_id");
    $query1->addLJoinClause("users_mediboard", "users_mediboard.user_id = evenement_ssr.therapeute_id");
    $query1->addLJoinClause("functions_mediboard", "functions_mediboard.function_id = users_mediboard.function_id");
    foreach ($ds->loadList($query1->makeSelect()) as $_row) {
      $stats->addTotal($_row["user_id"], $_row["refdate"], $_row["total"], "csarr");
    }

    // CdARR
    $query1 = clone($query);
    $query1->addTable("acte_cdarr");
    $query1->addLJoinClause("evenement_ssr", "acte_cdarr.evenement_ssr_id = evenement_ssr.evenement_ssr_id");
    $query1->addLJoinClause("sejour", "sejour.sejour_id = evenement_ssr.sejour_id");
    $query1->addLJoinClause("users_mediboard", "users_mediboard.user_id = evenement_ssr.therapeute_id");
    $query1->addLJoinClause("functions_mediboard", "functions_mediboard.function_id = users_mediboard.function_id");
    foreach ($ds->loadList($query1->makeSelect()) as $_row) {
      $stats->addTotal($_row["user_id"], $_row["refdate"], $_row["total"], "cdarr");
    }

    break;

  default:
    trigger_error("Type '$type' unknown", E_USER_WARNING);

    return;
}

$stats->display("CMediusersStats-SSR-$type");
