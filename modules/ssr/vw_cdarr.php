<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Ssr\CActiviteCdARR;
use Ox\Mediboard\Ssr\CTypeActiviteCdARR;

CCanDo::checkRead();

$activite       = new CActiviteCdARR();
$activite->code = CValue::getOrSession("code");
$activite->type = CValue::getOrSession("type");

// Pagination
$current = CValue::getOrSession("current", 0);
$step    = 20;

$type_activite = new CTypeActiviteCdARR();
$listTypes     = $type_activite->loadList(null, "code");

$where = array();
if ($activite->type) {
  $where["type"] = "= '$activite->type'";
}

$limit = "$current, $step";
$order = "type, code";
/** @var CActiviteCdARR[] $listActivites */
$listActivites = $activite->seek($activite->code, $where, $limit, true);
$total         = $activite->_totalSeek;

// Détail du chargement
foreach ($listActivites as $_activite) {
  $_activite->countElements();
  $_activite->countActes();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("activite", $activite);
$smarty->assign("listTypes", $listTypes);
$smarty->assign("listActivites", $listActivites);

$smarty->assign("current", $current);
$smarty->assign("step", $step);
$smarty->assign("total", $total);

$smarty->display("vw_cdarr");
