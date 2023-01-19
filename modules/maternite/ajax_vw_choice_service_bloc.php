<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$sejour_id = CView::get("sejour_id", "ref class|CSejour");
$location  = CView::get("location", "enum list|service|bloc");
CView::checkin();

$group = CGroups::loadCurrent();

$sejour = CSejour::findOrNew($sejour_id);
$curr_affectation = $sejour->loadRefCurrAffectation();
$curr_affectation->loadRefService();
$lit = $curr_affectation->loadRefLit();
$lit->loadCompleteView();
$last_operation = $sejour->loadRefLastOperation(true);
$last_operation->loadRefSalle();

$sejour->loadRefPatient();

$where = array();
$where["obstetrique"] = "= '1'";
$where["cancelled"]   = "= '0'";

$service  = new CService();
$services = $service->loadGroupList($where, "nom");

$chambres = CStoredObject::massLoadBackRefs($services, "chambres", "nom", array("annule" => "= '0'"));
CStoredObject::massLoadBackRefs($chambres, "lits", "nom", array("annule" => "= '0'"));

foreach ($services as $_service) {
  $_service->loadRefsChambres(false);
  foreach ($_service->_ref_chambres as $_chambre) {
    $_chambre->loadRefsLits(false);
  }
}

$bloc           = new CBlocOperatoire();
$bloc->group_id = $group->_id;
$bloc->type     = "obst";
$bloc->actif    = "1";
$blocs          = $bloc->loadMatchingList("nom");

CStoredObject::massLoadBackRefs($blocs, "salles", "nom", array("actif" => "= '1'"));

foreach ($blocs as $_bloc) {
  $_bloc->loadRefsSalles(array("actif" => "= '1'"));
}

//Vérification de la disponibilité des lits
$affectation     = new CAffectation();
$where           = [];
$where["entree"] = "<= '" . CMbDT::dateTime() . "'";
$where["sortie"] = ">= '" . CMbDT::dateTime() . "'";
$where["lit_id"] = "IS NOT NULL";
$services_ids    = [];
foreach ($services as $_service) {
    $services_ids[] = $_service->_id;
}
$where["service_id"] = CSQLDataSource::prepareIn($services_ids);
$affectations = $affectation->loadList($where);
foreach ($services as $_service) {
    foreach ($_service->_ref_chambres as $_chambre) {
        foreach ($_chambre->_ref_lits as $_lit) {
            foreach ($affectations as $_affectation) {
                if ($_lit->_id == $_affectation->lit_id) {
                    $_lit->_occupe = true;
                }
            }
        }
    }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("services", $services);
$smarty->assign("blocs"   , $blocs);
$smarty->assign("sejour"  , $sejour);
$smarty->assign("location", $location);
$smarty->display("inc_vw_choice_service_bloc");
