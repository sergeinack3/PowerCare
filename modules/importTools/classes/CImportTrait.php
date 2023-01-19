<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\ImportTools;



use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

/**
 * Description
 */
trait CImportTrait {
  /**
   * Find a consultation
   *
   * @param CPatient   $patient
   * @param string     $date
   * @param CMediusers $prat
   * @param string     $annule
   *
   * @return CConsultation
   * @throws Exception
   */
  protected function findConsult($patient, $date, $prat = null, $annule = '0', $relative_day_min = '2', $relative_day_max = '1') {
    // Recherche d'une consult qui se passe entre 2 jours avant ou 1 jour apres
    $date_min = CMbDT::date("-{$relative_day_min} DAYS", $date);
    $date_max = CMbDT::date("+{$relative_day_max} DAYS", $date);

    $consult = new CConsultation();
    $ds = $consult->getDS();

    $ljoin = array(
      "plageconsult" => "consultation.plageconsult_id = plageconsult.plageconsult_id",
    );

    $where = array(
      "consultation.patient_id" => $ds->prepare("= ?", $patient->_id),
      "consultation.annule"     => $ds->prepare("= ?", $annule),
      "plageconsult.date"       => $ds->prepare("BETWEEN ?1 AND ?2", $date_min, $date_max),
    );

    if ($prat && $prat->_id) {
      $where["plageconsult.chir_id"] = $ds->prepare("= ?", $prat->_id);
    }

    $consult->loadObject($where, null, null, $ljoin);

    return $consult;
  }

  /**
   * Make a consult
   *
   * @param integer     $patient_id Patient ID
   * @param string      $date       Consultation date
   * @param integer     $chir_id    Chir ID
   * @param bool|true   $store      Do we need to store the found consultation?
   * @param string|null $time       Consultation time
   * @param string      $freq       Freq of the consult if stored
   *
   * @return bool|CConsultation|null|string
   * @throws Exception
   */
  protected function makeConsult($patient_id, $date, $chir_id = null, $store = true, $time = null, $freq = "00:30:00") {
    $consult = new CConsultation;
    $ds = $consult->getDS();

    $date    = CMbDT::date($date);
    if (!$chir_id) {
      $chir_id = CMediusers::get()->_id;
    }

    $plage = new CPlageconsult();
    $where = array(
      "plageconsult.chir_id" => $ds->prepare("= ?", $chir_id),
      "plageconsult.date"    => $ds->prepare("= ?", $date),
    );

    $plage->loadObject($where);

    if (!$plage->_id) {
      $plage->date    = $date;
      $plage->chir_id = $chir_id;
      $plage->debut   = "09:00:00";
      $plage->fin     = "19:00:00";
      $plage->freq    = $freq;
      $plage->libelle = "Importation";

      if ($msg = $plage->store()) {
        CAppUI::setMsg($msg);

        return false;
      }
    }

    $consult->patient_id      = $patient_id;
    $consult->plageconsult_id = $plage->_id;
    $consult->heure           = ($time && $time > $plage->debut && $time < $plage->fin) ? $time : "09:00:00";
    $consult->chrono          = ($date < CMbDT::date() ? CConsultation::TERMINE : CConsultation::PLANIFIE);

    if ($store) {
      if ($msg = $consult->store()) {
        CAppUI::setMsg($msg);

        return false;
      }

      if (!$consult->_id) {
        return false;
      }
    }

    return $consult;
  }
}