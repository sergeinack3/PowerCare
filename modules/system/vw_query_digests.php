<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\System\CSQLQueryDigest;

CCanDo::checkRead();

if (!$ds = CSQLDataSource::get("cluster")) {
  CAppUI::stepMessage(UI_MSG_ERROR, "Unable to connect to cluster database");
  return;
}

$digest = new CSQLQueryDigest();
$digests = $digest->loadList(null, "ts_min DESC", 1);

$smarty = new CSmartyDP();
$smarty->assign("digests", $digests);
$smarty->display("vw_query_digests.tpl");
