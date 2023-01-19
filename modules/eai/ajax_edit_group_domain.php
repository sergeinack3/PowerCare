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
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Edit domain EAI
 */
CCanDo::checkAdmin();

$domain_id       = CValue::getOrSession("domain_id");
$group_domain_id = CValue::getOrSession("group_domain_id");

// Récupération du domaine à ajouter/editer 
$domain = new CDomain();
$domain->load($domain_id);

// Récupération de l'établissement du domaine à editer 
$group_domain = new CGroupDomain();
$group_domain->load($group_domain_id);

$groups = CGroups::loadGroups();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("domain"      , $domain);
$smarty->assign("group_domain", $group_domain);
$smarty->assign("groups"      , $groups);
$smarty->display("inc_edit_group_domain.tpl");
