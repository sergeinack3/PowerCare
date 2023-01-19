<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Cabinet\CPlageRessourceCab;
use Ox\Mediboard\Cabinet\CReservation;
use Ox\Mediboard\Cabinet\CReunion;

$plageconsult_id    = CView::post("plageconsult_id", "ref class|CPlageconsult");
$duree              = intval(CView::post("duree", "num"));
$chir_ids           = CView::post("chirs_ids", "str");
$ressources_ids     = CView::post("ressources_ids", "str");
$date               = CView::post("_date_planning", "date");
$heure              = CView::post("heure", "time");
$plage_ressource_id = CView::post("plage_ressource_id", "ref class|CPlageRessourceCab");
$absence_patient    = CView::post("no_patient", "bool default|0");
$motif_reunion      = CView::post("motif", "str"); // Var used when it's a meeting
$patient_id         = CView::post("patient_id", "ref class|CPatient");
$dialog             = CView::post("_dialog", "num");

// Si il n'y a pas de patient, alors on crée une réunion
if ($absence_patient) {
  $meeting        = new CReunion();
  $meeting->motif = $motif_reunion;
  $meeting->store();
  $_POST["reunion_id"] = $meeting->_id;
}

$save_post = $_POST;

$del = ($save_post["del"] || $save_post["annule"]);

$plage_consult = new CPlageconsult();
$plage_consult->load($plageconsult_id);

$plage_ressource = new CPlageRessourceCab();
$plage_ressource->load($plage_ressource_id);

$debut = $heure;

// before basic job, do the multiple consultations
CAppUI::requireModuleFile("dPcabinet", "controllers/do_consultation_multiple");

if ($consultation_ids = CValue::post("consultation_ids")) {
    $_POST_Temp = array(
        "consultation_ids" => CValue::post("consultation_ids"),
        "del"              => CValue::post("del"),
        "annule"           => CValue::post("annule", 0),
        "sejour_id"        => CValue::post("sejour_id"),
        "postRedirect"     => CValue::post("postRedirect"),
        "callback"         => CValue::post("callback"),
        "ajax"             => CValue::post("ajax"),
    );
    if (CValue::post("annule")) {
        $_POST_Temp["motif_annulation"] = CValue::post("motif_annulation");
        $_POST_Temp["rques"] = CValue::post("rques");
        $_POST_Temp["_cancel_sejour"] = CValue::post("_cancel_sejour");
    }
    $_POST = $_POST_Temp;

    $save_do = new CDoObjectAddEdit("CConsultation");
    $save_do->doIt();

    return;
}

// Si la granularité la plus grande est trouvée sur une plage de ressource
// alors on ajuste la durée de la première consultation
if ($plage_ressource->_id) {
  $fin = CMbDT::time("+ " . ($duree * (intval(CMbDT::minutesRelative("00:00:00", $plage_ressource->freq)))) . " minutes", $debut);

  $duree_calc = CMbDT::subTime($debut, $fin);

  $duree      = 0;
  $temp_debut = $debut;
  $temp_fin   = $debut;

  $freq = intval(CMbDT::minutesRelative("00:00:00", $plage_consult->freq));

  while (CMbDT::subTime($temp_debut, $temp_fin) < $duree_calc) {
    $temp_fin = CMbDT::time("+ $freq minutes", $temp_fin);
    $duree++;
  }
  $_POST["duree"] = $duree;
}
else {
  $fin        = CMbDT::time("+ " . ($duree * (intval(CMbDT::minutesRelative("00:00:00", $plage_consult->freq)))) . " minutes", $debut);
  $duree_calc = CMbDT::subTime($debut, $fin);
}

// Sauvegarde de la consultation avec la plus grande fréquence
$save_do = new CDoObjectAddEdit("CConsultation");
$save_do->doSingle(false);

if ($save_do->_obj instanceof CConsultation) {
  $reservation             = new CReservation();
  $reservation->patient_id = $save_do->_obj->patient_id;
  $reservation->date       = $save_do->_obj->loadRefPlageConsult()->date;
  $reservation->heure      = $heure;
  $reservations            = $reservation->loadMatchingListEsc();

  if (!$del) {
    $plage_ressource_ids = CStoredObject::massLoadFwdRef($reservations, "plage_ressource_cab_id");
    CStoredObject::massLoadFwdRef($plage_ressource_ids, "ressource_cab_id");
    foreach ($reservations as $_reservation) {
      if ($_reservation instanceof CReservation) {
        $_reservation->loadRefPlageRessource()->loadRefRessource();

        if (!in_array($_reservation->_ref_plage_ressource->_ref_ressource->_id, $ressources_ids)) {
          $_reservation->delete();
        }
      }
    }
  }
  else {
    $reservation = ($reservation && count($reservations) > 0) ? reset($reservations) : null;
    if ($reservation instanceof CReservation && $reservation->_id) {
      $reservation->delete();
    }
  }
}

// Sauvegarde des consultations suivantes avec arrondi du nombre de créneaux nécessaires
foreach ($chir_ids as $_chir_id) {
  if ($_chir_id == $plage_consult->chir_id) {
    $_POST = $save_post;
    continue;
  }

  $plage = new CPlageconsult();

  $where = [
    "chir_id" => "= '$_chir_id'",
    "date"    => "= '$date'",
    "'$heure' BETWEEN debut AND fin",
  ];

  if (!$plage->loadObject($where)) {
    continue;
  }

  $duree      = 0;
  $temp_debut = $debut;
  $temp_fin   = $debut;

  $freq = intval(CMbDT::minutesRelative("00:00:00", $plage->freq));

  while (CMbDT::subTime($temp_debut, $temp_fin) < $duree_calc) {
    $temp_fin = CMbDT::time("+ $freq minutes", $temp_fin);
    $duree++;
  }

  $_POST["plageconsult_id"] = $plage->_id;
  $_POST["duree"]           = $duree;

  $do = new CDoObjectAddEdit("CConsultation");
  $do->doSingle(false);

  if ($do->_obj instanceof CConsultation) {
    $reservation             = new CReservation();
    $reservation->patient_id = $do->_obj->patient_id;
    $reservation->date       = $do->_obj->loadRefPlageConsult()->date;
    $reservation->heure      = $heure;
    $reservations            = $reservation->loadMatchingListEsc();
    $plage_ressource_ids     = CStoredObject::massLoadFwdRef($reservations, "plage_ressource_cab_id");
    CStoredObject::massLoadFwdRef($plage_ressource_ids, "ressource_cab_id");
    foreach ($reservations as $_reservation) {
      if ($_reservation instanceof CReservation) {
        $_reservation->loadRefPlageRessource()->loadRefRessource();

        if (!in_array($_reservation->_ref_plage_ressource->_ref_ressource->_id, $ressources_ids)) {
          $_reservation->delete();
        }
      }
    }
  }

  $_POST = $save_post;
}

$reservations = [];

$reservation             = new CReservation();
$reservation->patient_id = $save_do->_obj->patient_id;
$reservation->date       = $save_do->_obj->loadRefPlageConsult()->date;
$reservation->heure      = $heure;
$reservations            = $reservation->loadMatchingListEsc();
$reservations_ids        = CMbArray::pluck($reservations, "_id");

if (is_countable($ressources_ids)) {
    foreach ($ressources_ids as $_ressource_id) {
        $plage                   = new CPlageRessourceCab();
        $plage->ressource_cab_id = $_ressource_id;
        $plage->date             = $date;
        $plage->loadMatchingObjectEsc();

        if (!$plage->_id) {
            continue;
        }

        $duree      = 0;
        $temp_debut = $debut;
        $temp_fin   = $debut;

        $freq = intval(CMbDT::minutesRelative("00:00:00", $plage->freq));

        while (CMbDT::subTime($temp_debut, $temp_fin) < $duree_calc) {
            $temp_fin = CMbDT::time("+ $freq minutes", $temp_fin);
            $duree++;
        }

        $reservation                         = new CReservation();
        $reservation->plage_ressource_cab_id = $plage->_id;
        $reservation->date                   = $date;
        $reservation->heure                  = $debut;
        $reservation->duree                  = $duree;
        $reservation->patient_id             = $save_do->_obj->patient_id;
        $reservation->loadMatchingObjectEsc();

        if ($reservation->_id || in_array($reservation->_id, $reservations_ids)) {
            continue;
        }

        if (!$del) {
            $msg = $reservation->store();

            CAppUI::setMsg($msg ?: CAppUI::tr("CReservation-msg-create"), $msg ? UI_MSG_ERROR : UI_MSG_OK);
        }
    }
}

// Redirection sur la première consultation créée
CValue::setSession($save_do->objectKey, $save_do->_obj->_id);
$save_do->redirect =
    'm=cabinet' . ($dialog ? '&dialog' : '&tab') . '=edit_planning&consultation_id=' . $save_do->_obj->_id;
$save_do->doRedirect();
