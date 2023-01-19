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
use Ox\Interop\Eai\CGroupDomain;
use Ox\Interop\Eai\CInteropActor;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Edit domain EAI
 */
CCanDo::checkAdmin();

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

// Liste des acteurs
$actor = new CInteropActor(); 
$actors = $actor->getObjects();

$group_domain = new CGroupDomain();

$groups = CGroups::loadGroups();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("domain"      , $domain);
$smarty->assign("actors"      , $actors);
$smarty->assign("group_domain", $group_domain);
$smarty->assign("groups"      , $groups);
$smarty->display("inc_edit_domain.tpl");
