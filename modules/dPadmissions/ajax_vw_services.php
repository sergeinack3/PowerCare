<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$sejour_id = CView::get("sejour_id", "ref class|CSejour");

CView::checkin();

// Chargement du séjour s'il y en a un
$sejour = new CSejour();
$sejour->load($sejour_id);

$service = $sejour->loadRefServiceMutation();

// Chargement des services
$order = "nom";
$services = $service->loadList(null, $order);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("service" , $service);
$smarty->assign("services", $services);

$smarty->display("inc_vw_services.tpl");
