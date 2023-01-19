<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkRead();
// Récupération des paramètres
$blocs_id = CView::get("blocs_id", "str", true);
$refresh  = CView::get("refresh", "bool default|0");
CView::checkin();

$blocs_selected = explode(",", $blocs_id);
CMbArray::removeValue("", $blocs_selected);
$group = CGroups::loadCurrent();

//Chargement de tous les blocs
$bloc           = new CBlocOperatoire();
$where          = array();
$where["actif"] = "= '1'";
$blocs          = $bloc->loadGroupList($where, "nom ASC", null, "bloc_operatoire_id");

$salle                       = new CSalle();
$ljoin                       = array();
$ljoin["bloc_operatoire"]    = "bloc_operatoire.bloc_operatoire_id = sallesbloc.bloc_id";
$where                       = array();
$where["sallesbloc.bloc_id"] = CSQLDataSource::prepareIn($blocs_selected);
$salles_bloc                 = $salle->loadGroupList($where, "sallesbloc.nom", null, "salle_id", $ljoin);

$ljoin                             = array();
$ljoin["bloc_operatoire"]          = "bloc_operatoire.bloc_operatoire_id = sallesbloc.bloc_id";
$ljoin["emplacement_salle"]         = "emplacement_salle.salle_id = sallesbloc.salle_id";
$where                             = array();
$where["sallesbloc.actif"]         = "= '1'";
$where["emplacement_salle.plan_x"]  = "IS NOT NULL";
$where["emplacement_salle.plan_y"]  = "IS NOT NULL";
$where["sallesbloc.bloc_id"]       = CSQLDataSource::prepareIn($blocs_selected);
$where["bloc_operatoire.group_id"] = "= '$group->_id'";
$salle_places                      = $salle->loadList($where, null, null, "salle_id", $ljoin);

$salles_non_placees = $salles_bloc;
if (count($salle_places)) {
  $ljoin                    = array();
  $ljoin["bloc_operatoire"] = "bloc_operatoire.bloc_operatoire_id = sallesbloc.bloc_id";

  $where                        = array();
  $where["sallesbloc.actif"]    = " = '1'";
  $where["sallesbloc.bloc_id"]  = CSQLDataSource::prepareIn($blocs_selected);
  $where["sallesbloc.salle_id"] = CSQLDataSource::prepareNotIn(array_keys($salle_places));
  $salles_non_placees           = $salle->loadGroupList($where, "bloc_operatoire.nom", null, "salle_id", $ljoin);
}

$salles_np = array();
foreach ($salles_non_placees as $_salle) {
  /* @var CSalle $_salle */
  $_salle->loadRefBloc();
  $_salle->loadRefEmplacementSalle();
  $salles_np[$_salle->bloc_id][] = $_salle;
}

$warning          = false;
$conf_nb_colonnes = CAppUI::gconf("dPhospi vue_topologique nb_colonnes_vue_topologique");
$grille           = array_fill(0, $conf_nb_colonnes, array_fill(0, $conf_nb_colonnes, array()));
if (count($blocs_selected)) {
  foreach ($salle_places as $_salle) {
    /* @var CSalle $_salle */
    $_salle->loadRefBloc();
    $emplacement_salle                                                = $_salle->loadRefEmplacementSalle();
    $grille[$emplacement_salle->plan_y][$emplacement_salle->plan_x][] = $_salle;

    if (count($grille[$emplacement_salle->plan_y][$emplacement_salle->plan_x]) > 1) {
      $warning = true;
    }
    if ($emplacement_salle->hauteur - 1) {
      for ($a = 0; $a <= $emplacement_salle->hauteur - 1; $a++) {
        if ($emplacement_salle->largeur - 1) {
          for ($b = 0; $b <= $emplacement_salle->largeur - 1; $b++) {
            if ($b != 0) {
              unset($grille[$emplacement_salle->plan_y + $a][$emplacement_salle->plan_x + $b]);
            }
            elseif ($a != 0) {
              unset($grille[$emplacement_salle->plan_y + $a][$emplacement_salle->plan_x + $b]);
            }
          }
        }
        elseif ($a < $emplacement_salle->hauteur - 1) {
          $c = $a + 1;
          unset($grille[$emplacement_salle->plan_y + $c][$emplacement_salle->plan_x]);
        }
      }
    }
    elseif ($emplacement_salle->largeur - 1) {
      for ($b = 1; $b <= $emplacement_salle->largeur - 1; $b++) {
        unset($grille[$emplacement_salle->plan_y][$emplacement_salle->plan_x + $b]);
      }
    }
  }
}

$bloc = null;
if (count($blocs_selected) == 1) {
  $bloc = $blocs[reset($blocs_selected)];
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("blocs"             , $blocs);
$smarty->assign("salles_non_placees", $salles_np);
$smarty->assign("blocs_selected"    , $blocs_selected);
$smarty->assign("grille"            , $grille);
$smarty->assign("bloc"              , $bloc);
$smarty->assign("warning"           , $warning);
$smarty->display("vw_plan_etage_blocs");
