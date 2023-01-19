<?php
/**
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CRemplacement;

CCanDo::checkRead();
$remplacement_id = CView::get("remplacement_id", "ref class|CRemplacement", true);
$user_id         = CView::get("user_id", "ref class|CMediusers", true);
CView::checkin();

//Chargement du remplacement
$remplacement = new CRemplacement();
$remplacement->load($remplacement_id);
$remplacement->loadRefsNotes();

if (!$remplacement->_id) {
  $remplacement->remplace_id = $user_id;
}

// Remplaçants disponibles
$user      = CMediusers::get($user_id);
$replacers = $user->loadUsers();

CStoredObject::massLoadFwdRef($replacers, "function_id");
foreach ($replacers as $_replacer) {
  $_replacer->loadRefFunction();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("remplacement", $remplacement);
$smarty->assign("replacers", $replacers);
$smarty->assign("user", $user);
$smarty->display("vw_edit_remplacement.tpl");
