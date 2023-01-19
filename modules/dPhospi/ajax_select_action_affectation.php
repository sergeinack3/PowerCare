<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CChambre;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;

CCanDo::checkRead();

$affectation_id = CView::get("affectation_id", "ref class|CAffectation");
$lit_id         = CView::get("lit_id", "ref class|CLit");
$chambre_id     = CView::get("chambre_id", "ref class|CChambre");

CView::checkin();

$affectation = new CAffectation();
$affectation->load($affectation_id);
$affectation->loadRefSejour();
$affectation->updateView();

$lit = new CLit();
$lit->load($lit_id);
$lit->loadRefChambre();

$chambre = new CChambre();
$chambre->load($chambre_id);
$chambre->loadRefService();
$chambre->loadRefsLits();

$service  = new CService();
$services = $service->loadGroupList();

// Création du template

$smarty = new CSmartyDP();

$smarty->assign("affectation", $affectation);
$smarty->assign("lit", $lit);
$smarty->assign("chambre", $chambre);
$smarty->assign("services", $services);

$smarty->display("inc_select_action_affectation.tpl");

