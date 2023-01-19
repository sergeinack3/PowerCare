<?php

/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();

$datetime_min = CView::get("_datetime_min", "dateTime");
$datetime_max = CView::get("_datetime_max", "dateTime");
$salle_id     = CView::get("salle_id", "ref class|CSalle");
$bloc_id      = CView::get("_bloc_id", "str");
$prat_id      = CView::get("_prat_id", "ref class|CMediusers");
$specialite   = CView::get("_specialite", "ref class|CFunctions");

CView::checkin();

if (is_array($bloc_id)) {
    CMbArray::removeValue("0", $bloc_id);
}

$plage = new CPlageOp();
$where = [];

$where["date"] = "BETWEEN '" . CMbDT::date($datetime_min) . "' AND '" . CMbDT::date($datetime_max) . "'";

if (!$prat_id && !$specialite) {
    $function = new CFunctions();
    $user     = CMediusers::get();
    if (!$user->isFromType(["Anesthésiste"])) {
        $functions  = $function->loadListWithPerms(PERM_READ);
        $praticiens = $user->loadPraticiens();
    } else {
        $functions  = $function->loadList();
        $praticiens = $praticien->loadList();
    }
    $where[] = "plagesop.chir_id " . CSQLDataSource::prepareIn(array_keys($praticiens)) .
        " OR plagesop.spec_id " . CSQLDataSource::prepareIn(array_keys($functions));
}

if ($prat_id) {
    $where["chir_id"] = "= '$prat_id'";
}

if ($specialite) {
    $where["spec_id"] = "= '$specialite'";
}

$salle                 = new CSalle();
$whereSalle            = [];
$whereSalle["bloc_id"] = CSQLDataSource::prepareIn(
    count($bloc_id) ?
        $bloc_id :
        array_keys(CGroups::loadCurrent()->loadBlocs(PERM_READ))
);

if ($salle_id) {
    $whereSalle["sallesbloc.salle_id"] = "= '$salle_id'";
}
$listSalles = $salle->loadListWithPerms(PERM_READ, $whereSalle);

$where["salle_id"] = CSQLDataSource::prepareIn(array_keys($listSalles));
$order             = "date, salle_id, debut";

/** @var CPlageOp[] $plages */
$plages = $plage->loadList($where, $order);

$planning = [];

// @todo : gérer les hors plages

CStoredObject::massLoadBackRefs($plages, "affectations_personnel");

foreach ($plages as $_plage) {
    $affectations = $_plage->loadAffectationsPersonnel();

    /** @var COperation[] $operations */
    $operations = $_plage->loadRefsOperations(0);

    CStoredObject::massLoadFwdRef($operations, "plageop_id");
    $praticiens = CStoredObject::massLoadFwdRef($operations, "chir_id");
    CStoredObject::massLoadFwdRef($praticiens, "function_id");
    $sejours = CStoredObject::massLoadFwdRef($operations, "sejour_id");
    CStoredObject::massLoadFwdRef($sejours, "patient_id");

    foreach ($operations as $key => $_operation) {
        $_operation->loadRefPlageOp();
        if (
            $_operation->_datetime_best < $datetime_min || $_operation->_datetime_best > $datetime_max
        ) {
            unset($operations[$key]);
            continue;
        }
        $_operation->loadRefPatient();
        $_operation->loadRefChir()->loadRefFunction();
        $_operation->updateSalle();
    }

    $_date_view = $_plage->date . " - $_plage->debut";

    if (count($affectations)) {
        foreach ($affectations as $_affectations_by_type) {
            foreach ($_affectations_by_type as $_affectation) {
                $_affectation->loadRefPersonnel()->loadRefUser();

                $_user_view = $_affectation->_ref_personnel->_ref_user->_view;

                if (!isset($planning[$_user_view])) {
                    $planning[$_user_view] = [];
                }
                if (!isset($planning[$_user_view][$_date_view]) && count($operations)) {
                    $planning[$_user_view][$_date_view] = [];
                }
                if (count($operations)) {
                    $planning[$_user_view][$_date_view] = $operations;
                }
            }
        }
    }

    CStoredObject::massLoadBackRefs($operations, "affectations_personnel");

    foreach ($operations as $_operation) {
        // Personnels ajoutés
        $affectations = $_operation->loadAffectationsPersonnel();

        if (count($affectations)) {
            foreach ($affectations as $_affectations_by_type) {
                foreach ($_affectations_by_type as $_affectation) {
                    $_affectation->loadRefPersonnel()->loadRefUser();

                    $_user_view = $_affectation->_ref_personnel->_ref_user->_view;

                    if (!isset($planning[$_user_view])) {
                        $planning[$_user_view] = [];
                    }
                }
            }
        }
    }
}

// Trier le planning par personnel
ksort($planning);

$smarty = new CSmartyDP();

$smarty->assign("datetime_min", $datetime_min);
$smarty->assign("datetime_max", $datetime_max);
$smarty->assign("planning", $planning);

$smarty->display("print_planning_personnel");
