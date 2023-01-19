<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CEmplacementSalle;

CCanDo::checkRead();
$salle_id = CView::get("salle_id", "ref class|CSalle", true);
$bloc_id  = CView::get("bloc_id", "ref class|CBlocOperatoire", true);
CView::checkin();

$emplacement_salle = new CEmplacementSalle();
$where             = array();
$where["salle_id"] = " = '$salle_id'";
$emplacement_salle->loadObject($where);

$bloc = new CBlocOperatoire();
$bloc->load($bloc_id);
$salles = $bloc->loadRefsSalles();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("emplacement_salle", $emplacement_salle);
$smarty->assign("salles"           , $salles);
$smarty->display("inc_vw_emplacement_salle");
