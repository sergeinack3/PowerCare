<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Ssr\CRHS;

CCanDo::checkEdit();

$services_ids    = CService::getServicesIdsPref(null);
$show_np = false;
if (array_key_exists("NP", $services_ids)) {
  unset($services_ids["NP"]);
  $show_np = true;
}
CView::checkin();

// Liste des RHSs
$group_id = CGroups::loadCurrent()->_id;

$rhs = new CRHS();
$req = new CRequest;
$req->addTable("rhs");
$req->addLJoinClause("sejour", "sejour.sejour_id = rhs.sejour_id");
$req->addColumn("date_monday", "mondate");
$req->addColumn("COUNT(*)", "count");
$req->addWhereClause("rhs.facture", " = '0'");
$req->addWhereClause("sejour.annule", " = '0'");
$req->addWhereClause("sejour.group_id", " = '$group_id'");
$req->addWhereClause(null, ($show_np || !count($services_ids) ? "sejour.service_id IS NULL OR " : "")
  . "sejour.service_id " . CSQLDataSource::prepareIn($services_ids));
$req->addGroup("date_monday");
$ds         = $rhs->_spec->ds;
$rhs_counts = $ds->loadList($req->makeSelect());
foreach ($rhs_counts as &$_rhs_count) {
  $_rhs_count["sundate"] = CMbDT::date("+6 DAYS", $_rhs_count["mondate"]);
}

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("rhs_counts", $rhs_counts);
$smarty->display("vw_facturation_rhs");
