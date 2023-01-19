<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Admin\CSourceLDAP;

CCanDo::checkAdmin();

$source_ldap = new CSourceLDAP();
$sources_ldap = $source_ldap->loadList(null, "priority DESC");

$sources_ldap[] = $source_ldap; // to create a new one

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("sources_ldap", $sources_ldap);
$smarty->display("inc_sources_ldap.tpl");
