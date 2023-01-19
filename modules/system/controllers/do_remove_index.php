<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CObjectIndexer;
use Ox\Core\CView;

CCanDo::checkAdmin();
$index_name = CView::post('index_name', 'str notNull');
CView::checkin();

if (!$index_name) {
  CAppUI::setMsg('common-error-Missing parameter', UI_MSG_ERROR);
  CApp::rip();
}

$nb_keys = CObjectIndexer::removeIndex($index_name);

CAppUI::setMsg("$nb_keys removed");
echo CAppUI::getMsg();
CApp::rip();