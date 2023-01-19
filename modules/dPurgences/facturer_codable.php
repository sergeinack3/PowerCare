<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\CValue;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkAdmin();

global $dPconfig;
$dPconfig["sa"]["trigger_sejour"] = "facture";
$limit                            = CValue::get("limit", 10);
$day                              = CValue::get("day", 3);

$sejour = new CSejour();
$ds     = $sejour->getDS();

$request = new CRequest();
$request->addSelect("sejour.sejour_id");
$request->addTable("sejour");
$request->addLJoinClause("rpu", "rpu.sejour_id = sejour.sejour_id");
$request->addWhereClause("annule", "!='1'");
$request->addWhereClause("facture", "!='1'");
$request->addWhereClause("sortie_reelle", "BETWEEN '" . CMbDT::dateTime("-{$day}DAY") . "' AND '" . CMbDT::dateTime() . "'");
$request->addWhereClause("rpu.sejour_id", "IS NOT NULL");
$request->addGroup("sejour.sejour_id");
$request->addHaving("count(*) = 1");
$request->setLimit($limit);

$list_sejour = $ds->loadList($request->makeSelect());

foreach ($list_sejour as $_sejour_id) {
  $sejour = new CSejour();
  $sejour->load($_sejour_id["sejour_id"]);
  if (!$nda = $sejour->getTagNDA()) {
    continue;
  }
  $rpu = $sejour->loadRefRPU();
  if ($rpu->mutation_sejour_id) {
    $sejour_reliquat = $rpu->loadRefSejourMutation();
    if (!$nda = $sejour_reliquat->getTagNDA()) {
      continue;
    }
    $consultations = $sejour_reliquat->loadRefsConsultations();

    foreach ($consultations as $_consultation) {
      $_consultation->facture = "1";
      $_consultation->store();
    }
  }

  $sejour->facture = "1";
  $sejour->store();
}