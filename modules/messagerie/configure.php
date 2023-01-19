<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Admin\CSourceLDAP;

CCanDo::checkAdmin();

$source = new CSourceLDAP();
$source->name = 'messagerie ldap directory';
$source->loadMatchingObject();

if (!$source->_id) {
  $source->rootdn = 'dc=mssante,dc=fr';
}

$sources = array($source);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign('sources_ldap', $sources);
$smarty->display("configure.tpl");