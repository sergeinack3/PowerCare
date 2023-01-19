<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CMessage;

CCanDo::checkRead();

$_status = CValue::get("_status");

// Chargement des messages
$filter = new CMessage();
$filter->_status = $_status;
$messages = $filter->loadPublications($filter->_status);
foreach ($messages as $_message) {
  $_message->loadRefsNotes();
  $_message->loadRefModuleObject();
  $_message->loadRefGroup();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("filter", $filter);
$smarty->assign("messages", $messages);
$smarty->display("inc_list_messages.tpl");
