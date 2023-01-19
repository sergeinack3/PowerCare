<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Admin\CSourceLDAP;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

$groups  = CGroups::loadGroups(PERM_EDIT);
$sources = CSourceLDAP::loadSources();

CStoredObject::massLoadBackRefs($sources, 'source_ldap_links');

foreach ($sources as $_source) {
  $_source->loadRefSourceLDAPLinks();
}

$smarty = new CSmartyDP();
$smarty->assign('sources', $sources);
$smarty->assign('groups', $groups);
$smarty->display('inc_list_ldap_sources');
