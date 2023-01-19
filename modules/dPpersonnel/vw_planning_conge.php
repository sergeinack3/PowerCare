<?php
/**
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// CCanDo::checkRead();
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPlageConge;

$choix       = CView::get("choix", "str default|mois");
$affiche_nom = CView::get("affiche_nom", "bool default|1");
$type_view   = CView::get("type_view", "enum list|conge|remplacement default|conge");

$filter             = new CPlageConge();
$filter->user_id    = CView::get("user_id", "ref class|CMediusers default|" . CAppUI::$user->_id);
$filter->date_debut = CView::get("date_debut", "date default|now");
CView::checkin();

$mediuser  = new CMediusers();
$mediusers = $mediuser->loadListFromType();

if (!$filter->date_debut) {
  $filter->date_debut = CMbDT::date();
}

$smarty = new CSmartyDP();

$smarty->assign("filter", $filter);
$smarty->assign("choix", $choix);
$smarty->assign("mediusers", $mediusers);
$smarty->assign("affiche_nom", $affiche_nom);
$smarty->assign("type_view", $type_view);

$smarty->display("vw_planning_conge.tpl");
