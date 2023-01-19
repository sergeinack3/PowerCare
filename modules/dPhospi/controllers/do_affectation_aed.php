<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$_lock_all_lits          = CView::post("_lock_all_lits", "bool");
$_lock_all_lits_urgences = CView::post("_lock_all_lits_urgences", "bool");
$lit_id                  = CView::post("lit_id", "ref class|CLit");
$entree                  = CView::post("entree", "dateTime");
$sortie                  = CView::post("sortie", "dateTime");
$function_id             = CView::post("function_id", "ref class|CFunctions");
$rques                   = CView::post("rques", "str");
$sejour_id               = CView::post("sejour_id", "ref class|CSejour");
$affectation_id          = CView::post("affectation_id", "ref class|CAffectation");
$_mod_mater              = CView::post("_mod_mater", "bool default|0");

CView::checkin();

if ($_lock_all_lits || $_lock_all_lits_urgences) {
    /** @var CLit $lit */
    $lit = new CLit();
    $lit = $lit->load($lit_id);
    $lit->loadRefChambre()->loadRefService()->loadRefsChambres();

    foreach ($lit->_ref_chambre->_ref_service->_ref_chambres as $chambre) {
        $chambre->loadRefsLits();
        foreach ($chambre->_ref_lits as $lit) {
            // Recherche d'une ou plusieurs affectations existantes en collision avant de créer le blocage
            $aff_temp        = new CAffectation();
            $where["lit_id"] = "= '$lit->_id'";
            $where[]         = "entree < '$sortie' AND sortie > '$entree'";

            // Si collision, on passe au lit suivant
            if ($aff_temp->countList($where)) {
                continue;
            }
            $affectation         = new CAffectation();
            $affectation->lit_id = $lit->_id;
            $affectation->entree = $entree;
            $affectation->sortie = $sortie;
            $affectation->rques  = $rques;
            if ($_lock_all_lits_urgences) {
                $affectation->function_id = $function_id;
            }
            if ($msg = $affectation->store()) {
                CAppUI::setMsg($msg, UI_MSG_ERROR);
            }
        }
    }

    echo CAppUI::getMsg();
    CApp::rip();
}

$tolerance = CAppUI::gconf("dPhospi CAffectation create_affectation_tolerance");

//Si on est en création d'afectation et qu'il y a un sejour_id
if (!$affectation_id && $sejour_id) {
    $sejour = new CSejour();
    $sejour->load($sejour_id);
    $curr_affectation = $sejour->loadRefCurrAffectation();
    //On modifie au lieu de créer une affectation si l'afectation courante ne dépasse pas la tolérance
    if ($curr_affectation && $curr_affectation->_id) {
        if (CMbDT::addDateTime("00:$tolerance:00", $curr_affectation->entree) > $entree) {
            $_POST["affectation_id"] = $curr_affectation->_id;

            if ($_mod_mater) {
                $_POST["effectue"] = 1;
            }
        }
    }
}

$do = new CDoObjectAddEdit("CAffectation");
$do->doIt();
