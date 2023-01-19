<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Stats\CTempsPrepa;

CView::enforceSlave();

$total["nbPrep"]   = 0;
$total["nbPlages"] = 0;
$total["somme"]    = 0;
$total["moyenne"]  = 0;


$where            = array();
$where["chir_id"] = CSQLDataSource::prepareIn(array_keys($listPrats));

$ljoin          = array();
$ljoin["users"] = "users.user_id = temps_prepa.chir_id";

$order = "users.user_last_name ASC, users.user_first_name ASC";

$tempPrepa = new CTempsPrepa();
/** @var CTempsPrepa[] $listTemps */
$listTemps = $tempPrepa->loadList($where, $order, null, null, $ljoin);

foreach ($listTemps as $temps) {
  $temps->loadRefPraticien();
  $temps->_ref_praticien->loadRefFunction();
  $total["nbPrep"]   += $temps->nb_prepa;
  $total["nbPlages"] += $temps->nb_plages;
  $total["somme"]    += $temps->nb_prepa * strtotime($temps->duree_moy);
}
if ($total["nbPrep"] != 0) {
  $total["moyenne"] = $total["somme"] / $total["nbPrep"];
}
