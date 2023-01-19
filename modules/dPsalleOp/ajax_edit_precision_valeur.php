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
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\SalleOp\CPrecisionValeur;

CCanDo::checkEdit();
$precision_valeur_id = CView::get("precision_valeur_id", "ref class|CPrecisionValeur");
$precision_id        = CView::get("precision_id", "ref class|CGestePeropPrecision");
CView::checkin();

$group = CGroups::loadCurrent();

$precision_valeur = new CPrecisionValeur();
$precision_valeur->load($precision_valeur_id);

// Select current group for a new object
if (!$precision_valeur->_id) {
  $precision_valeur->group_id                 = $group->_id;
  $precision_valeur->geste_perop_precision_id = $precision_id;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("precision_valeur", $precision_valeur);
$smarty->display("inc_edit_precision_valeur");
