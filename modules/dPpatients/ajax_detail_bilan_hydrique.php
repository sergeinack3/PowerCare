<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$sejour_id   = CView::get("sejour_id", "ref class|CSejour");
$datetime    = CView::get("datetime", "dateTime");
$granularite = CView::get("granularite", "num");

CView::checkin();

$bilan = array(
  "entrees" => array(),
  "sorties" => array(),
);

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

// Granularité
$affectations = $sejour->loadSurrAffectations();

$host = $affectations["curr"]->_id ? $affectations["curr"] : $affectations["prev"];
$host = $host->loadRefService();

if (!$host->_id) {
  $host = $sejour->loadRefEtablissement();
}

$datetime_min = $datetime;
$datetime_max = CMbDT::dateTime("+$granularite HOURS", $datetime_min);

$total = array(
  "total"   => 0,
  "entrees" => 0,
  "sorties" => 0
);

// Partie constantes médicales (en clé le datetime max pour avoir une ligne par constante)
$bh_cst = CConstantesMedicales::getValeursHydriques($sejour, $datetime_min, $datetime_max);

foreach ($bh_cst as $dateTime => $_bh_cst) {
  foreach ($_bh_cst["detail"] as $_cst => $value) {
    $key = $value >= 0 ? "entrees" : "sorties";

    $name = CAppUI::tr("CConstantesMedicales-$_cst-court");

    if (!isset($bilan[$key][$datetime_max][$name])) {
      $bilan[$key][$datetime_max][$name] = array(
        "name" => $name,
        "value" => 0
      );
    }

    $bilan[$key][$datetime_max][$name]["value"] += $value;

    $total["total"] += $value;
    $total[$key]    += $value;
  }
}

// Partie prescription
$prescription = $sejour->loadRefPrescriptionSejour();

if ($prescription && $prescription->_id) {
  $bh_perfs = $prescription->calculEntreesHydriques($granularite, $datetime_min, $datetime_max);

  foreach ($bh_perfs as $datetime => $_bh_perfs) {
    foreach ($_bh_perfs as $_bh_perf) {
      $bilan["entrees"][$datetime][] = array(
        "name"  => $_bh_perf["name"],
        "value" => $_bh_perf["value"]
      );

      $total["total"]   += $_bh_perf["value"];
      $total["entrees"] += $_bh_perf["value"];
    }
  }
}

ksort($bilan["entrees"]);
ksort($bilan["sorties"]);

$before = CMbDT::dateTime("-$granularite HOURS", $datetime_min);
$after  = $datetime_max;

$smarty = new CSmartyDP();

$smarty->assign("bilan", $bilan);
$smarty->assign("datetime_min", $datetime_min);
$smarty->assign("datetime_max", $datetime_max);
$smarty->assign("total", $total);
$smarty->assign("before", $before);
$smarty->assign("after", $after);

$smarty->display("inc_detail_bilan_hydrique");