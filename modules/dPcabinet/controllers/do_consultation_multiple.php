<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

//global
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CConsultationCategorie;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Cabinet\CPlageRessourceCab;
use Ox\Mediboard\Cabinet\CReservation;

$patient_id                    = CValue::post("patient_id");
$rques                         = CValue::post("rques");
$motif                         = CValue::post("motif");
$chrono                        = CValue::post("chrono");
$premiere                      = CValue::post("premiere");
$pause                         = CValue::post("_pause");
$nb_semaines                   = CValue::post("nb_semaines");
$ressources_ids                = CValue::post("ressources_ids");
$duree                         = intval(CValue::post("duree"));
$_uf_medicale_id               = CView::post('_uf_medicale_id', 'ref class|CUniteFonctionnelle');
$_uf_soins_id                  = CView::post('_uf_soins_id', 'ref class|CUniteFonctionnelle');
$_charge_id                    = CView::post('_charge_id', 'ref class|CChargePriceIndicator');
$_operation_id                 = CView::post('_operation_id', 'ref class|COpeation');
$sejour_id                     = CView::post('sejour_id', 'ref class|CSejour');
$grossesse_id                  = CView::post('grossesse_id', 'ref class|CGrossesse');
$_force_create_sejour          = CView::post('_force_create_sejour', 'bool default|0');
$_create_sejour_activite_mixte = CView::post('_create_sejour_activite_mixte', 'bool default|0');
$teleconsultation              = CView::post('teleconsultation', 'bool default|0');

if (!$nb_semaines) {
  $nb_semaines = 0;
}

for ($i = 0; $i <= $nb_semaines; $i++) {
  for ($a = 1; $a <= CAppUI::pref("NbConsultMultiple"); $a++) {
    $_consult_id       = CValue::post("consult_id_$a");
    $_heure            = CValue::post("heure_$a");
    $_plage_id         = CValue::post("plage_id_$a");
    $_date             = CValue::post("date_$a");
    $_chir_id          = CValue::post("chir_id_$a");
    $_rques            = CValue::post("rques_$a");
    $_docs_necessaires = CValue::post("docs_necessaires_$a");
    $_cancel           = CValue::post("cancel_$a", 0);
    $_precription_id   = CValue::post("element_prescription_id_$a");
    $_category_id      = CValue::post("categorie_id_$a");

    // Répétition
    if ($i > 0 && $_heure && $_chir_id) {
      $_date = CMbDT::date("+1 week", $_date);

      $plage          = new CPlageconsult();
      $plage->date    = $_date;
      $plage->chir_id = $_chir_id;
      $plage->loadObject();
      $_plage_id = $plage->_id;
    }

    if ($_heure && $_plage_id && $_chir_id) {
      $consult = new CConsultation();
      if ($_consult_id) {
        $consult->load($_consult_id);
      }
      if (!$pause) {
        $consult->patient_id = $patient_id;
      }
      else {
        $consult->patient_id = null;
      }

      if ($_category_id) {
        $cat = new CConsultationCategorie();
        $cat->load($_category_id);
        if ($cat->_id) {
          $consult->duree        = $duree = $cat->duree;
          $consult->categorie_id = $cat->_id;
        }
      }

      // Compute duration
      $plage_freq          = new CPlageconsult();
      $plage_freq->date    = $_date;
      $plage_freq->chir_id = $_chir_id;
      $plage_freq->loadMatchingObjectEsc();

      $relative = CMbDT::minutesRelative("00:00:00", $plage_freq->freq);
      $fin        = CMbDT::time("+ " . ($duree * (intval($relative))) . " minutes", $_heure);
      $duree_calc = CMbDT::subTime($_heure, $fin);

      $duree      = 0;
      $temp_debut = $_heure;
      $temp_fin   = $_heure;

      while (CMbDT::subTime($temp_debut, $temp_fin) < $duree_calc) {
        $temp_fin = CMbDT::time("+ $relative minutes", $temp_fin);
        $duree++;
      }

        $consult->plageconsult_id               = $_plage_id;
        $consult->heure                         = $_heure;
        $consult->duree                         = $duree;
        $consult->motif                         = $motif;
        $consult->rques                         = $_rques ? "$rques\n$_rques" : $rques;
        $consult->docs_necessaires              = $_docs_necessaires;
        $consult->chrono                        = $chrono;
        $consult->premiere                      = $premiere;
        $consult->annule                        = $_cancel;
        $consult->element_prescription_id       = $_precription_id;
        $consult->_uf_medicale_id               = $_uf_medicale_id;
        $consult->_uf_soins_id                  = $_uf_soins_id;
        $consult->_charge_id                    = $_charge_id;
        $consult->_operation_id                 = $_operation_id;
        $consult->sejour_id                     = $sejour_id;
        $consult->grossesse_id                  = $grossesse_id;
        $consult->_force_create_sejour          = $_force_create_sejour;
        $consult->_create_sejour_activite_mixte = $_create_sejour_activite_mixte;
        $consult->teleconsultation              = $teleconsultation;
        $consult->_hour                         = null;
        $consult->_min                          = null;

      if (count($ressources_ids)) {
        $consult->groupee = 1;
      }

      if ($msg = $consult->store()) {
        CAppUI::setMsg(CAppUI::tr("CConsultation") . "$a :" . $msg, UI_MSG_ERROR);
      }

      if (count($ressources_ids) && $consult->_id) {
        $reservations = [];

        $reservation             = new CReservation();
        $reservation->patient_id = $consult->patient_id;
        $reservation->date       = $plage->date;
        $reservation->heure      = $consult->heure;
        $reservations            = $reservation->loadMatchingListEsc();
        $reservations_ids        = CMbArray::pluck($reservations, "_id");

        foreach ($ressources_ids as $_ressource_id) {
          $plage                   = new CPlageRessourceCab();
          $plage->ressource_cab_id = $_ressource_id;
          $plage->date             = $_date;
          $plage->loadMatchingObjectEsc();

          if (!$plage->_id) {
            continue;
          }

          $reservation                         = new CReservation();
          $reservation->plage_ressource_cab_id = $plage->_id;
          $reservation->date                   = $_date;
          $reservation->heure                  = $_heure;
          $reservation->duree                  = $duree;
          $reservation->patient_id             = $patient_id;
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
    }
  }
}

echo CAppUI::getMsg();
