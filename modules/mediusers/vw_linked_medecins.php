<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$user_id = CView::get('user_id', 'ref class|CMediusers notNull');

CView::checkin();

$mediuser = new CMediusers();
$mediuser->load($user_id);
if (!$user_id || !$mediuser->_id) {
  CAppUI::stepAjax('CMediusers.none', UI_MSG_ERROR);
}

$medecins = $mediuser->loadBackRefs('medecin', 'nom, prenom, ville', 100);

$smarty = new CSmartyDP();
$smarty->assign('mediuser', $mediuser);
$smarty->assign('medecins', $medecins);
$smarty->display('vw_linked_medecins.tpl');