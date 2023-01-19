<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Ssr\CEvenementSSR;

CCando::checkEdit();
global $m, $current_m;

if (!isset($current_m)) {
  $current_m = CView::setSession("current_m", $m);
}
$date    = CView::get("date", "date default|now");
$kine_id = CView::get("kine_id", "ref class|CMediusers default|" . CAppUI::$instance->user_id, true);
CView::checkin();

// Chargement de la liste des utilisateurs
$group = CGroups::loadCurrent();
$kines = CEvenementSSR::loadRefExecutants($group->_id);

$kine = new CMediusers();
$kine->load($kine_id);
$kine->loadRefIntervenantCdARR();

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("kine"     , $kine);
$smarty->assign("kines"    , $kines);
$smarty->assign("kine_id"  , $kine_id);
$smarty->assign("current_m", $current_m);
$smarty->display("vw_kine_board");
