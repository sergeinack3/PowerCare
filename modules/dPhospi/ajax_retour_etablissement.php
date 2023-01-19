<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CService;

CCanDo::checkRead();

$affectation_id      = CView::get("affectation_id", "ref class|CAffectation");
$affectation_perm_id = CView::get("affectation_perm_id", "ref class|CAffectation");
$from_placement      = CView::get("from_placement", "bool");

CView::checkin();

// Chargement de l'affectation précédent la permission
$affectation = new CAffectation();
$affectation->load($affectation_id);
$affectation->loadRefLit();
$affectation->loadRefService();

// Chargement du séjour
$sejour = $affectation->loadRefSejour();
$sejour->loadRefPatient();

// Liste des services
$service = new CService();

$where = [
  "cancelled" => "= '0'",
  "externe"   => "= '0'"
];

$services = $service->loadGroupList($where);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("services", $services);
$smarty->assign("affectation", $affectation);
$smarty->assign("affectation_perm_id", $affectation_perm_id);
$smarty->assign("from_placement", $from_placement);

$smarty->display("inc_retour_etablissement");
