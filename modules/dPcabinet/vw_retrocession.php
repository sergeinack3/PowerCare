<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;

CCanDo::checkRead();

// Période
$filter = new CPlageconsult();
$filter->_date_min  = CValue::getOrSession("_date_min");
$filter->_date_max  = CValue::getOrSession("_date_max");

// Filtre sur les praticiens
$chir_id = CValue::getOrSession("chir");
$listPrat = CConsultation::loadPraticiensCompta($chir_id);

$plageconsult = new CPlageconsult();
$ljoin = array();
$ljoin["consultation"] = "consultation.plageconsult_id = plageconsult.plageconsult_id";
$where = array();

$where[] = "plageconsult.remplacant_id ".CSQLDataSource::prepareIn(array_keys($listPrat))."
  OR plageconsult.pour_compte_id ".CSQLDataSource::prepareIn(array_keys($listPrat))."
  OR (plageconsult.chir_id ".CSQLDataSource::prepareIn(array_keys($listPrat))." AND remplacant_id IS NOT NULL)";
$where[] = "plageconsult.date >= '$filter->_date_min' AND plageconsult.date <= '$filter->_date_max'";

$where["consultation.annule"] = "= '0'";
$order = "chir_id ASC";

/** @var CPlageconsult[] $listPlages */
$listPlages = $plageconsult->loadList($where, $order, null, null, $ljoin);

$plages = array();

foreach ($listPlages as $plage) {
  $plage->loadRefsConsultations();
  $plages[$plage->_id]["total"] = 0;
  foreach ($plage->_ref_consultations as $consult) {
    $consult->loadRefPatient();
    $plages[$plage->_id]["total"] += $consult->du_patient * $plage->pct_retrocession/100; 
  }
}
// Création du template
$smarty = new CSmartyDP();

$smarty->assign("listPrat"    , $listPrat);
$smarty->assign("listPlages"  , $listPlages);
$smarty->assign("filter"      , $filter);
$smarty->assign("plages"      , $plages);

$smarty->display("vw_retrocession.tpl");
