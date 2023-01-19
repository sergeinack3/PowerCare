<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Ssr\CEquipement;
use Ox\Mediboard\Ssr\CEvenementSSR;
use Ox\Mediboard\System\CPlanningEvent;
use Ox\Mediboard\System\CPlanningWeek;

global $m;

CCando::checkRead();

$date      = CValue::getOrSession("date", CMbDT::date());
$sejour_id = CValue::get("sejour_id");

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$equipement = new CEquipement;
$equipement->load(CValue::get("equipement_id"));

if (!$equipement->visualisable) {
  echo "<div class='small-info'>L'équipement <strong>$equipement->_view</strong> n'est pas visualisable</div>";
  CApp::rip();
}

$see_name_element_planning = CAppUI::gconf("ssr general see_name_element_planning");

$nb_days_planning = CEvenementSSR::getNbJoursPlanning(null, $date);
$planning         = new CPlanningWeek($date, null, null, $nb_days_planning, false, "auto", false, true);
$planning->title  = "Equipement '$equipement->_view'";
$planning->guid   = $equipement->_guid;

// Chargement des evenement SSR
$evenement              = new CEvenementSSR();
$where["debut"]         = "BETWEEN '$planning->_date_min_planning 00:00:00' AND '$planning->_date_max_planning 23:59:59'";
$where["equipement_id"] = " = '$equipement->_id'";

/** @var CEvenementSSR[] $evenements */
$evenements = $evenement->loadList($where);
foreach ($evenements as $_evenement) {
  $important = !$sejour_id || $_evenement->sejour_id == $sejour_id;

  $sejour  = $_evenement->loadRefSejour();
  $patient = $sejour->loadRefPatient();

  // Title
  $therapeute = $_evenement->loadRefTherapeute();
  $title      = ucfirst(strtolower($patient->nom)) . "  $therapeute->_shortview";

  // Color
  $function = $therapeute->loadRefFunction();
  $color    = "#$function->color";

  // Classes
  $css_classes = array();

  // Prescription case
  if ($line = $_evenement->loadRefPrescriptionLineElement()) {
    $element  = $line->_ref_element_prescription;
    $category = $element->loadRefCategory();
    $title    = ($m != "psy") ? $category->_view : "";
    $title    .= ($m != "psy" && $see_name_element_planning) ? " - " : "";
    $title    .= ($m == "psy" || $see_name_element_planning) ? $element->_view : "";

    // Color
    $color = $element->_color ? "#$element->_color" : null;

    // CSS Class
    $css_classes[] = $element->_guid;
  }

  $event = new CPlanningEvent($_evenement->_guid, $_evenement->debut, $_evenement->duree, $title, $color, $important, $css_classes);
  $planning->addEvent($event);
}

$planning->showNow();
$planning->rearrange(true);

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("planning", $planning);
$smarty->display("inc_vw_week");
