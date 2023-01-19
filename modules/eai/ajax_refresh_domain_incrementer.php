<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Eai\CDomain;

/**
 * View interop receiver EAI
 */
CCanDo::checkRead();

$domain_id = CValue::getOrSession("domain_id");
$domain    = new CDomain();
$domain->load($domain_id);
$domain->loadRefsGroupDomains();
$domain->loadRefIncrementer()->loadView();
$domain->isMaster();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("domain", $domain);
$smarty->display("inc_vw_domain_incrementer.tpl");