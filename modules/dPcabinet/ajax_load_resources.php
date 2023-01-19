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
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CReservation;
use Ox\Mediboard\Cabinet\CRessourceCab;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$practitioner_id = CView::getRefCheckRead("practitioner_id", "ref class|CMediusers");
$appointment_id  = CView::getRefCheckRead("appointment_id", "ref class|CConsultation");
$date            = CView::get("date", "date");
$hour            = CView::get("hour", "time");
$function_id     = CView::getRefCheckRead("function_id", "ref class|CFunction");

CView::checkin();

$practitioner = CMediusers::findOrFail($practitioner_id);

$ds = CSQLDataSource::get('std');

// Get reservations
$ljoin = [
  "plage_ressource_cab" => "reservation.plage_ressource_cab_id = plage_ressource_cab.plage_ressource_cab_id",
  "ressource_cab"       => "plage_ressource_cab.ressource_cab_id = ressource_cab.ressource_cab_id",
];
$where = [
  "reservation.date"          => $ds->prepare("= ?", $date),
  "reservation.heure"         => $ds->prepare("= ?", $hour),
  "ressource_cab.function_id" => $ds->prepare("= ?", $practitioner->function_id),
];

$reservations = (new CReservation())->loadList($where, null, null, null, $ljoin);

$reserved_resources = [];
foreach ($reservations as $_reservation) {
  if ($_reservation->loadRefPlageRessource()) {
    $reserved_resources[] = $_reservation->_ref_plage_ressource->loadRefRessource();
  }
}

// Get ressources
$ljoin = ["plage_ressource_cab" => "plage_ressource_cab.ressource_cab_id = ressource_cab.ressource_cab_id"];
$where = [
  "actif"                     => $ds->prepare("= ?", "1"),
  "plage_ressource_cab.date"  => $ds->prepare("= ?", $date),
  "plage_ressource_cab.debut" => $ds->prepare("< ?", $hour),
  "plage_ressource_cab.fin"   => $ds->prepare("> ?", $hour),
  "function_id"               => $ds->prepare("= ?", $function_id ?? $practitioner->function_id),
];

$resources = (new CRessourceCab())->loadList($where, null, null, null, $ljoin);

$selected_resources = [];
$appointment        = null;

if ($appointment_id) {
  $appointment        = CConsultation::findOrFail($appointment_id);
  $selected_resources = $appointment->loadRefReservedRessources();

  // Get inactive resources linked to the appointment (to delete it for example)
  $ljoin     = [
    "plage_ressource_cab" => "plage_ressource_cab.ressource_cab_id = ressource_cab.ressource_cab_id",
    "reservation"         => "reservation.plage_ressource_cab_id = plage_ressource_cab.plage_ressource_cab_id",
  ];
  $where     = [
    "ressource_cab.actif"    => "= '0'",
    "reservation.patient_id" => $ds->prepare("= ?", $appointment->patient_id),
    "reservation.date"       => $ds->prepare("= ?", $date),
    "reservation.heure"      => $ds->prepare("= ?", $hour),
  ];
  $resources += (new CRessourceCab())->loadList($where, null, null, null, $ljoin);
}

$smarty = new CSmartyDP();
$smarty->assign("unavailable_resources", $reserved_resources);
$smarty->assign("resources", $resources);
$smarty->assign("selected_resources", $selected_resources);
$smarty->assign("appointment", $appointment);
$smarty->assign("refresh", 1);
$smarty->display("inc_resources_list");
