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
use Ox\Interop\Eai\CGroupDomain;

/**
 * View interop actors EAI
 */
CCanDo::checkAdmin();

$domain_id = CValue::get("domain_id");

// Domaine
$domain = new CDomain();
$domain->load($domain_id);
$domain->loadRefsGroupDomains();
foreach ($domain->_ref_group_domains as $_group_domain) {
  $_group_domain->loadRefGroup();  
}

$group_domain = new CGroupDomain();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("domain"      , $domain);
$smarty->assign("group_domain", $group_domain);
$smarty->display("inc_vw_group_domains.tpl");

