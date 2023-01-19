<?php
/**
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPlageConge;

CCanDo::checkRead();

$plage_id = CView::get("plage_id", "ref class|CPlageConge");
$user_id  = CView::get("user_id", "ref class|CMediusers", true);
$is_modal = CView::get("is_modal", "bool default|0");
CView::checkin();

$user = CMediusers::get($user_id);

// Chargement de la plage
$plageconge = new CPlageConge();
$plageconge->user_id = $user_id;
$plageconge->load($plage_id);
$plageconge->loadRefsNotes();

// Remplaçants disponibles
$replacers = $user->loadUsers();
unset($replacers[$user->_id]);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("user", $user);
$smarty->assign("plageconge", $plageconge);
$smarty->assign("replacers", $replacers);
$smarty->assign("is_modal", $is_modal);
$smarty->display("inc_edit_plage_conge.tpl");
