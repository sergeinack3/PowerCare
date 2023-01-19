<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * View mediusers
 */
CCanDo::checkRead();

$filter  = CValue::getOrSession("filter", "");
$user_id = CValue::get("user_id");
$type    = CValue::getOrSession("_user_type");
$locked  = CValue::getOrSession("locked");

//ldap
$no_association         = CValue::get("no_association");
$ldap_user_actif        = CValue::get("ldap_user_actif");
$ldap_user_deb_activite = CValue::get("ldap_user_deb_activite");
$ldap_user_fin_activite = CValue::get("ldap_user_fin_activite");

// Récupération des fonctions
$group = CGroups::loadCurrent();
$group->loadFunctions();

$mediuser = new CMediusers();

CMbArray::naturalSort(CUser::$types);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("mediuser", $mediuser);
$smarty->assign("utypes", CUser::$types);
$smarty->assign("filter", $filter);
$smarty->assign("user_id", $user_id);
$smarty->assign("type", $type);
$smarty->assign("locked", $locked);
$smarty->assign("group", $group);
$smarty->assign("no_association", $no_association);
$smarty->assign("ldap_user_actif", $ldap_user_actif);
$smarty->assign("ldap_user_deb_activite", $ldap_user_deb_activite);
$smarty->assign("ldap_user_fin_activite", $ldap_user_fin_activite);
$smarty->display("vw_idx_mediusers.tpl");
