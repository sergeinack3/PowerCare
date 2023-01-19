<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CTarif;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$tarif_ids  = CView::post('tarif_ids', 'str notNull');
$mode       = CView::post('mode', 'enum list|CMediusers|CFunctions notNull');
$prat_id    = CView::post('prat_id', 'ref class|CMediusers notNull');

CView::checkin();

$user = CMediusers::get($prat_id);
$tarif_ids = explode('|', $tarif_ids);
$errors = 0;
$success = 0;

foreach ($tarif_ids as $tarif_id) {
  $tarif = new CTarif();
  $tarif->load($tarif_id);
  if ($tarif->_id) {
    switch ($mode) {
      case 'CFunctions':
        $tarif->function_id = $user->function_id;
        $tarif->chir_id = '';
        $tarif->_type = 'function';
        break;
      case 'CMediusers':
      default:
        $tarif->chir_id = $user->_id;
        $tarif->function_id = '';
        $tarif->_type = 'chir';
    }

    if ($msg = $tarif->store()) {
      $errors++;
    }
    else {
      $success++;
    }
  }
  else {
    $errors++;
  }
}

if ($errors) {
  CAppUI::setMsg("{$errors} tarifs n'ont pas pu être basculés", UI_MSG_ERROR);
}

if ($success) {
  CAppUI::setMsg("{$success} tarifs modifiés");
}

echo CAppUI::getMsg();