<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Stats\CTempsHospi;

$codeCCAM = strtoupper(CValue::getOrSession("codeCCAM", ""));
$prat_id  = CValue::getOrSession("prat_id", 0);
$type     = CValue::getOrSession("type", "ambu");

CView::enforceSlave();

$total["nbSejours"]   = 0;
$total["duree_moy"]   = 0;
$total["duree_somme"] = 0;


$listTemps = new CTempsHospi;

$where = array();
$ds    = CSQLDataSource::get("std");

if ($type) {
  $where["type"] = $ds->prepare("= %", $type);
}
$where["nb_sejour"]    = ">= '$nb_sejour_mini'";
$where["praticien_id"] = CSQLDataSource::prepareIn(array_keys($listPrats), $prat_id);

if ($codeCCAM) {
  $codeCCAM     = trim($codeCCAM);
  $listCodeCCAM = explode(" ", $codeCCAM);
  $listCodeCCAM = array_filter($listCodeCCAM);

  foreach ($listCodeCCAM as $keyccam => $code) {
    $where[] = "ccam LIKE '%$code%'";
  }
}

$ljoin          = array();
$ljoin["users"] = "users.user_id = temps_hospi.praticien_id";
$order          = "users.user_last_name ASC, users.user_first_name ASC, ccam";

/** @var CTempsHospi[] $listTemps */
$listTemps = $listTemps->loadList($where, $order, null, null, $ljoin);

if ($codeCCAM) {
  // Groupement des données par chirurgien
  $old_praticien        = 0;
  $TempsHospitalisation = array();

  foreach ($listTemps as $keyTemps => $temps) {
    if ($old_praticien != $temps->praticien_id) {
      // Si on change de chirurgien, alors on initialise la variable
      $TempsHospitalisation[$temps->praticien_id]               = new CTempsHospi();
      $TempsHospitalisation[$temps->praticien_id]->praticien_id = $temps->praticien_id;
      $TempsHospitalisation[$temps->praticien_id]->nb_sejour    = 0;
      $TempsHospitalisation[$temps->praticien_id]->duree_moy    = 0;
      $TempsHospitalisation[$temps->praticien_id]->duree_ecart  = 0;
      $TempsHospitalisation[$temps->praticien_id]->ccam         = $codeCCAM;
    }

    $TempsHospitalisation[$temps->praticien_id]->nb_sejour += $temps->nb_sejour;
    $TempsHospitalisation[$temps->praticien_id]->duree_moy += $temps->nb_sejour * $temps->duree_moy;

    $old_praticien = $temps->praticien_id;
  }

  $listTemps = $TempsHospitalisation;
}


foreach ($listTemps as $keyTemps => $temps) {
  if ($codeCCAM) {
    $temps->duree_moy = $temps->duree_moy / $temps->nb_sejour;
  }

  $listTemps[$keyTemps]->loadRefPraticien();
  $listTemps[$keyTemps]->_ref_praticien->loadRefFunction();
  $total["nbSejours"]   += $temps->nb_sejour;
  $total["duree_somme"] += $temps->nb_sejour * $temps->duree_moy;
}

if ($total["nbSejours"] != 0) {
  $total["duree_moy"] = $total["duree_somme"] / $total["nbSejours"];
}
