<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\CDomain;

/**
 * View interop receiver EAI
 */
CCanDo::checkRead();

$domain_id = CView::get("domain_id", "ref class|CDomain", true);

CView::checkin();

// Récupération du domaine à ajouter/editer
$domain = new CDomain();
$domain->load($domain_id);
$domain->loadRefsGroupDomains();
foreach ($domain->_ref_group_domains as $_group_domain) {
  $_group_domain->loadRefGroup();
}
$domain->loadRefActor();
$domain->loadRefIncrementer()->loadView();
$domain->isMaster();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("domain", $domain);
$smarty->display("vw_list_domain.tpl");