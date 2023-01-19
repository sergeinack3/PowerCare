<?php
/**
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Mediboard\Context\CContextualIntegrationLocation;

$helps = CContextualIntegrationLocation::loadContextHelp(false);

foreach ($helps as $_help) {
  $_help->_ref_integration->_url = $_help->_ref_integration->url;
}

$smarty = new CSmartyDP();
$smarty->assign("locations", $helps);
$smarty->display("vw_helps");