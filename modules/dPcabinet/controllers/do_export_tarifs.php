<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CTarif;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkAdmin();

$chir_id      = CView::post('chir_id', 'ref class|CMediusers');
$function_id  = CView::post('function_id', 'ref class|CFunctions');
$group_id     = CView::post('group_id', 'ref class|CGroups');

CView::checkin();

$object = CGroups::get();
if ($chir_id) {
  $object = CMediusers::get($chir_id);
}
elseif ($function_id) {
  $object = CFunctions::loadFromGuid("CFunctions-$function_id");
}
elseif ($group_id) {
  $object = CGroups::get($group_id);
}

$file = CTarif::exportTarifsFor($object);
$file->stream(CAppUI::tr('CTarif') . '_' . str_replace(' ', '_', $object->_view));
CApp::rip();