<?php 
/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Cim10\CFavoriCIM10;

CCanDo::checkAdmin();

$user_id = CView::get('user_id', 'ref class|CMediusers notNull');

CView::checkin();

$favori = new CFavoriCIM10();

$favori->favoris_user = $user_id;

$count = $favori->countMatchingList();

if ($user_id || $function_id) {
  $data = array('count' => $count);
}
else {
  $data = array('count' => 0);
}

CApp::json($data);