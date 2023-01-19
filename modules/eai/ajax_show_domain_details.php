<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Eai\CDomain;

$domain_id = CValue::get("domain_id");

$domain = new CDomain();
$domain->load($domain_id);

$domain->countObjects();

$smarty = new CSmartyDP();
$smarty->assign("domain", $domain);
$smarty->display("inc_show_domain_details.tpl");