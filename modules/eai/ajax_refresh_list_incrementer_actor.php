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
use Ox\Interop\Eai\CInteropActor;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Refresh incrementer/actor EAI
 */
CCanDo::checkAdmin();

$domain_id = CValue::get("domain_id");

// Liste des domaines
$domain = new CDomain();
$domain->load($domain_id);
$domain->loadRefsGroupDomains();
$domain->loadRefActor();
$domain->loadRefIncrementer()->loadView();
$domain->isMaster();

// Liste des acteurs
$actor  = new CInteropActor();
$actors = $actor->getObjects();

$groups = CGroups::loadGroups();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("domain", $domain);
$smarty->assign("actors", $actors);
$smarty->assign("groups", $groups);
$smarty->display("inc_vw_domain_actor.tpl");
