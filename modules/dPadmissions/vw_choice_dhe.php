<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;

$sejour_id = CView::get("sejour_id", "ref class|CSejour");
CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

// Chargement des interventions
$whereOperations = array("annulee" => "= '0'");
$sejour->loadRefsOperations($whereOperations);
$sejour->loadRefPraticien();

$smarty = new CSmartyDP();
$smarty->assign("sejour", $sejour);
$smarty->display("vw_choice_dhe.tpl");
