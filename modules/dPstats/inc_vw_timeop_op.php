<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Stats\CTempsOp;

$codeCCAM = strtoupper(CValue::getOrSession("codeCCAM", ""));
$prat_id  = CValue::getOrSession("prat_id", 0);

CView::enforceSlave();

$total["nbInterventions"] = 0;
$total["estim_moy"]       = 0;
$total["estim_somme"]     = 0;
$total["occup_moy"]       = 0;
$total["occup_somme"]     = 0;
$total["duree_moy"]       = 0;
$total["duree_somme"]     = 0;
$total["reveil_moy"]      = 0;
$total["reveil_somme"]    = 0;


$listTemps = new CTempsOp;

$where                    = array();
$where["nb_intervention"] = ">= '$nb_sejour_mini'";
$where["chir_id"]         = CSQLDataSource::prepareIn(array_keys($listPrats), $prat_id);

if ($codeCCAM) {
  $codeCCAM     = trim($codeCCAM);
  $listCodeCCAM = explode(" ", $codeCCAM);
  $listCodeCCAM = array_filter($listCodeCCAM);

  foreach ($listCodeCCAM as $keyccam => $code) {
    $where[] = "ccam LIKE '%$code%'";
  }
}

$ljoin          = array();
$ljoin["users"] = "users.user_id = temps_op.chir_id";
$order          = "users.user_last_name ASC, users.user_first_name ASC, ccam";

/** @var CTempsOp[] $listTemps */
$listTemps = $listTemps->loadList($where, $order, null, null, $ljoin);

if ($codeCCAM) {
  // Groupement des données par chirurgien
  $old_chir        = 0;
  $TempsOperatoire = array();

  foreach ($listTemps as $keyTemps => $temps) {
    if ($old_chir != $temps->chir_id) {
      // Si on change de chirurgien, alors on initialise la variable
      $old_temps_id                                      = $temps->temps_op_id;
      $TempsOperatoire[$temps->chir_id]                  = new CTempsOp();
      $TempsOperatoire[$temps->chir_id]->chir_id         = $temps->chir_id;
      $TempsOperatoire[$temps->chir_id]->nb_intervention = 0;
      $TempsOperatoire[$temps->chir_id]->estimation      = 0;
      $TempsOperatoire[$temps->chir_id]->occup_moy       = 0;
      $TempsOperatoire[$temps->chir_id]->duree_moy       = 0;
      $TempsOperatoire[$temps->chir_id]->duree_ecart     = "-";
      $TempsOperatoire[$temps->chir_id]->occup_ecart     = "-";
    }

    $TempsOperatoire[$temps->chir_id]->ccam            = str_replace("|", ", ", $codeCCAM);
    $TempsOperatoire[$temps->chir_id]->nb_intervention += $temps->nb_intervention;
    $TempsOperatoire[$temps->chir_id]->estimation      += $temps->nb_intervention * strtotime($temps->estimation);
    $TempsOperatoire[$temps->chir_id]->occup_moy       += $temps->nb_intervention * strtotime($temps->occup_moy);
    $TempsOperatoire[$temps->chir_id]->duree_moy       += $temps->nb_intervention * strtotime($temps->duree_moy);
    $TempsOperatoire[$temps->chir_id]->reveil_moy      += $temps->nb_intervention * strtotime($temps->reveil_moy);

    $old_chir = $temps->chir_id;
  }

  $listTemps = $TempsOperatoire;
}


foreach ($listTemps as $keyTemps => $temps) {
  if ($codeCCAM) {
    $temps->estimation = CMbDT::strftime("%H:%M:%S", $temps->estimation / $temps->nb_intervention);
    $temps->occup_moy  = CMbDT::strftime("%H:%M:%S", $temps->occup_moy / $temps->nb_intervention);
    $temps->duree_moy  = CMbDT::strftime("%H:%M:%S", $temps->duree_moy / $temps->nb_intervention);
    $temps->reveil_moy = CMbDT::strftime("%H:%M:%S", $temps->reveil_moy / $temps->nb_intervention);
  }

  $listTemps[$keyTemps]->loadRefPraticien();
  $listTemps[$keyTemps]->_ref_praticien->loadRefFunction();
  $total["nbInterventions"] += $temps->nb_intervention;
  $total["estim_somme"]     += $temps->nb_intervention * strtotime($temps->estimation);
  $total["occup_somme"]     += $temps->nb_intervention * strtotime($temps->occup_moy);
  $total["duree_somme"]     += $temps->nb_intervention * strtotime($temps->duree_moy);
  $total["reveil_somme"]    += $temps->nb_intervention * strtotime($temps->reveil_moy);
}

if ($total["nbInterventions"] != 0) {
  $total["estim_moy"]  = $total["estim_somme"] / $total["nbInterventions"];
  $total["occup_moy"]  = $total["occup_somme"] / $total["nbInterventions"];
  $total["duree_moy"]  = $total["duree_somme"] / $total["nbInterventions"];
  $total["reveil_moy"] = $total["reveil_somme"] / $total["nbInterventions"];
}
