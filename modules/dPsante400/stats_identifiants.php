<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;

CCanDo::checkEdit();
CView::enforceSlave();

// Statistiques sur les id400
$req = new CRequest;
$req->addTable("id_sante400");
$req->addColumn("COUNT(DISTINCT object_id)", "nbObjects");
$req->addColumn("COUNT(id_sante400_id)", "nbID400s");

$ds        = CSQLDataSource::get("std");
$statTotal = $ds->loadList($req->makeSelect());
$statTotal = $statTotal[0];

$req->addSelect("object_class");
$req->addGroup("object_class");
$stats = $ds->loadList($req->makeSelect());

// Computes average ID400 count per object
foreach ($stats as &$stat) {
  $stat["average"] = $stat["nbID400s"] / $stat["nbObjects"];
}

$statTotal["average"] = @($statTotal["nbID400s"] / $statTotal["nbObjects"]);


// Création du template
$smarty = new CSmartyDP();
$smarty->assign("stats", $stats);
$smarty->assign("statTotal", $statTotal);
$smarty->display("stats_identifiants.tpl");