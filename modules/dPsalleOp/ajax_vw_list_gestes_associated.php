<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\SalleOp\CAnesthPeropCategorie;

CCanDo::checkRead();
$categorie_id             = CView::get("categorie_id", "ref class|CAnesthPeropCategorie");
$protocole_geste_perop_id = CView::get("protocole_geste_perop_id", "ref class|CProtocoleGestePerop");
$show_only                = CView::get("show_only", "bool default|1");
CView::checkin();

$evenement_categorie = CAnesthPeropCategorie::find($categorie_id);
$evenement_categorie->loadRefsGestesPerop();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("evenement_categorie"     , $evenement_categorie);
$smarty->assign("protocole_geste_perop_id", $protocole_geste_perop_id);
$smarty->assign("show_only"               , $show_only);
$smarty->display("inc_vw_list_gestes_associated");
