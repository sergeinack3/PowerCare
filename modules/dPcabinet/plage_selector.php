<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;

CCanDo::check();

global $period, $periods, $listPraticiens, $chir_id, $function_id, $date_range, $ndate, $pdate, $heure, $plageconsult_id, $consultation_id;

$period          = CView::get("period", "str default|" . CAppUI::pref("DefaultPeriod"));
$periods         = array("day", "week", "month","weekly");
$chir_id         = CView::getRefCheckEdit("chir_id", "ref class|CMediusers");
$function_id     = $chir_id ? null : CView::getRefCheckEdit("function_id", "ref class|CFunctions");
$date_range      = CView::get("date", "date default|now");
$plageconsult_id = CView::get("plageconsult_id", "ref class|CPlageconsult");
$consultation_id = CView::getRefCheckEdit("consultation_id", "ref class|CConsultation");
$heure           = CView::get("heure", "str");
CView::checkin();

// Vérification des droits sur les praticiens
$listPraticiens = CConsultation::loadPraticiens(PERM_EDIT);

// Récupération des consultations de la plage séléctionnée
$plage = new CPlageconsult;
if ($plageconsult_id) {
  $plage->load($plageconsult_id);
  $date_range = $plage->date;
}

// Récupération de la periode précédente et suivante
$unit = $period;
if ($period == "weekly") {
  $unit = "week";
}

if ($period == "month") {
  $ndate = CMbDT::date("first day of next month"   , $date_range);
  $pdate = CMbDT::date("last day of previous month", $date_range);
}
else {
  $ndate = CMbDT::date("+1 $unit", $date_range);
  $pdate = CMbDT::date("-1 $unit", $date_range);
}

if ($period == "weekly") {
  CAppUI::requireModuleFile("dPcabinet", "inc_plage_selector_weekly");
}
else {
  CAppUI::requireModuleFile("dPcabinet", "inc_plage_selector_classic");
}
