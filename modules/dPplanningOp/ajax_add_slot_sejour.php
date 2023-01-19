<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$rank   = CView::get("rank"  , "num");
$entree = CView::get("entree", "dateTime");
$sortie = CView::get("sortie", "dateTime");
$_hour_entree_prevue = CView::get("_hour_entree_prevue", "str");
$_min_entree_prevue  = CView::get("_min_entree_prevue", "str");
$_hour_sortie_prevue = CView::get("_hour_sortie_prevue", "str");
$_min_sortie_prevue  = CView::get("_min_sortie_prevue", "str");

CView::checkin();

$sejour = new CSejour();
$sejour->entree_prevue = $entree;
$sejour->sortie_prevue = $sortie;

if ($entree && $sortie) {
  $sejour->updateFormFields();
}
else {
  $sejour->_hour_entree_prevue = $_hour_entree_prevue;
  $sejour->_min_entree_prevue = $_min_entree_prevue;
  $sejour->_hour_sortie_prevue = $_hour_sortie_prevue;
  $sejour->_min_sortie_prevue = $_min_sortie_prevue;
  $sejour->_id = "temp"; // Id temporaire pour que seuls les form fields ci-dessus soient pris en compte
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);
$smarty->assign("rank"  , $rank);

$smarty->display("inc_add_slot_sejour");