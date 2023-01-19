<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();

$date    = CView::get("date", "date default|now", true);
$bloc_id = CView::get("bloc_id", "ref class|CBlocOperatoire", true);
$sspi_id = CView::get("sspi_id", "ref class|CSSPI", true);

CView::checkin();

$use_poste = CAppUI::conf("dPplanningOp COperation use_poste");

$ds = CSQLDataSource::get("std");

// Selection des salles du bloc
$salle      = new CSalle();
$whereSalle = array("bloc_id" => " = '$bloc_id'");
$listSalles = $salle->loadListWithPerms(PERM_READ, $whereSalle);

// Chargement des interventions
$operation = new COperation();

$join = array();
$join["sejour"]  = "sejour.sejour_id = operations.sejour_id";

$where_preop   = array();
$where_encours = array();
$where_ops     = array();
$where_reveil  = array();
$where_out     = array();
$countArray    = array();

$where_preop["annulee"]                 = "= '0'";
$where_preop["operations.salle_id"] = CSQLDataSource::prepareIn(array_keys($listSalles));
$where_preop[]                          = "operations.date = '$date'";
$where_preop["operations.entree_salle"] = "IS NULL";
$where_preop["operations.sortie_salle"] = "IS NULL";
$where_preop[] = "sejour.type <> 'exte'";

$where_encours["annulee"]                 = "= '0'";
$where_encours["operations.salle_id"] = CSQLDataSource::prepareIn(array_keys($listSalles));
$where_encours[]                          = "operations.date = '$date'";
$where_encours["operations.entree_salle"] = "IS NOT NULL";
$where_encours["operations.sortie_salle"] = "IS NULL";
$where_encours[] = "sejour.type <> 'exte'";

$where_ops["annulee"]                           = "= '0'";
$where_ops["operations.salle_id"] = CSQLDataSource::prepareIn(array_keys($listSalles));
$where_ops[]                                    = "operations.date = '$date'";
$where_ops["operations.sortie_salle"]           = "IS NOT NULL";
$where_ops["operations.entree_reveil"]          = "IS NULL";
$where_ops["operations.sortie_reveil_possible"] = "IS NULL";
$where_ops["operations.sortie_sans_sspi"] = "IS NULL";
$where_ops[] = "sejour.type <> 'exte'";

$where_reveil["annulee"]                       = "= '0'";
if ($use_poste) {
  $where_reveil[] = "(operations.poste_sspi_id IS NOT NULL AND (poste_sspi.sspi_id = '$sspi_id' OR poste_sspi.sspi_id IS NULL))
                  OR (operations.poste_sspi_id IS NULL AND (operations.sspi_id = '$sspi_id' OR operations.sspi_id IS NULL)
                      AND operations.salle_id ". CSQLDataSource::prepareIn(array_keys($listSalles)) . ")";
}
else {
  $where_reveil["operations.salle_id"] = CSQLDataSource::prepareIn(array_keys($listSalles));
}
$where_reveil[]                                = "operations.date = '$date'";
$where_reveil["operations.entree_reveil"]      = "IS NOT NULL";
$where_reveil["operations.sortie_reveil_reel"] = "IS NULL";
$where_reveil[] = "sejour.type <> 'exte'";

$where_out["annulee"]                       = "= '0'";
if ($use_poste) {
  $where_out[] = "(operations.poste_sspi_id IS NOT NULL AND (poste_sspi.sspi_id = '$sspi_id' OR poste_sspi.sspi_id IS NULL))
               OR (operations.poste_sspi_id IS NULL AND (operations.sspi_id = '$sspi_id' OR operations.sspi_id IS NULL)
                   AND operations.salle_id ". CSQLDataSource::prepareIn(array_keys($listSalles)) . ")";
}
else {
  $where_out["operations.salle_id"] = CSQLDataSource::prepareIn(array_keys($listSalles));
}
$where_out[]                                = "operations.date = '$date'";
$where_out[] = "operations.sortie_reveil_reel IS NOT NULL OR operations.sortie_sans_sspi IS NOT NULL";
$where_out[] = "sejour.type <> 'exte'";

$list = array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0);
$nb_sorties_non_realisees = array(3 => 0, 4 => 0);

foreach (array($where_preop ,$where_encours, $where_ops, $where_reveil, $where_out) as $key => $_where) {
  $request = new CRequest();
  $request->addSelect("operations.operation_id, operations.chir_id, operations.sortie_reveil_reel");
  $request->addTable("operations");
  if ($use_poste) {
    $request->addLJoin("poste_sspi ON poste_sspi.poste_sspi_id = operations.poste_sspi_id");
  }
  $request->addLJoin("sejour ON sejour.sejour_id = operations.sejour_id");

  //si preference à oui uniquement utilisateur courant (responsable SSPI)
  if (CAppUI::pref("pec_sspi_current_user") && $key == 3) {
    $curr_user = CMediusers::get();
    $request->addLJoin("affectation_personnel ON affectation_personnel.object_id = operations.operation_id");
    $request->addLJoin("personnel ON personnel.personnel_id = affectation_personnel.personnel_id");
    $request->addWhere("affectation_personnel.object_class = 'COperation' AND personnel.user_id = '$curr_user->_id'");
  }

  $request->addWhere($_where);

  $list[$key] = $ds->loadHashAssoc($request->makeSelect());

  if (count($list[$key])) {
    foreach (CMbArray::pluck($list[$key], "chir_id") as $chir_id) {
      if (!CMediusers::get($chir_id)->canDo()->read) {
        foreach ($list[$key] as $_operation_id => $_operation) {
          if ($_operation["chir_id"] == $chir_id) {
            unset($list[$key][$_operation_id]);
          }
        }
      }
    }

    if (in_array($key, array(3, 4))) {
      foreach ($list[$key] as $operation_content) {
        if (!$operation_content["sortie_reveil_reel"]) {
          $nb_sorties_non_realisees[$key]++;
        }
      }
    }
  }
}

$use_reveil_reel = CAppUI::gconf("dPsalleOp COperation use_sortie_reveil_reel");

// Compteur
$countArray["preop"]   = count($list[0]);
$countArray["encours"] = count($list[1]);
$countArray["ops"]     = count($list[2]);
$countArray["reveil"]  = $use_reveil_reel ? array(count($list[3]), $nb_sorties_non_realisees[3]) : count($list[3]);
$countArray["out"]     = $use_reveil_reel ? array(count($list[4]), $nb_sorties_non_realisees[4]) : count($list[4]);

CApp::json($countArray);
