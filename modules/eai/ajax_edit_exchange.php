<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();

$exchange_guid = CView::get("exchange_guid", "str");

CView::checkin();

if (!$exchange_guid) {
  CAppUI::displayAjaxMsg("Pas d'objet passé en paramètre");
  CApp::rip();
}

$exchange = CMbObject::loadFromGuid($exchange_guid);

if (!$exchange || $exchange && !$exchange->_id) {
  CAppUI::displayAjaxMsg("Aucun échange trouvé");
  CApp::rip();
}

$exchange->loadRefsNotes();

$smarty = new CSmartyDP();
$smarty->assign("exchange", $exchange);
$smarty->display("inc_edit_exchange.tpl");