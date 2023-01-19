<?php
/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Search\CSearchHistory;

CCanDo::checkEdit();

$user = CMediusers::get();

$search_history = new CSearchHistory();
$where          = array('user_id' => '=' . $user->user_id);
$ids            = $search_history->loadColumn('search_history_id', $where);

$retour = $search_history->deleteAll($ids);

CAppUI::stepAjax("mod-search-history-deleted", UI_MSG_OK);

CApp::rip();