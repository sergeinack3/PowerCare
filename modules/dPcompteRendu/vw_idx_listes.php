<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CListeChoix;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Interface des listes de choix
 */
CCanDo::checkRead();

$filtre = new CListeChoix();
$filtre->user_id     = CView::get("user_id", "num", true);
$filtre->function_id = CView::get("function_id", "num", true);

CView::checkin();

if (!$filtre->user_id && !$filtre->function_id) {
  $filtre->user_id = CMediusers::get()->_id;
}

$filtre->loadRefUser();
$filtre->loadRefFunction();

$module = CModule::getActive("dPcompteRendu");
$is_admin = $module && $module->canAdmin();
$access_function = $is_admin || CAppUI::gconf("dPcompteRendu CListeChoix access_function");

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("filtre"         , $filtre);
$smarty->assign("access_function", $access_function);

$smarty->display("vw_idx_listes");
