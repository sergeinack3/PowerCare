<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Tools;

use Ox\Core\CMbDT;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * trait CConsultationTrait
 * Consultation utilities EAI
 */
trait CConsultationTrait
{
    /**
     * Search consultation with document
     *
     * @param string       $dateTime     date
     * @param CPatient     $patient      patient
     * @param string|null  $praticien_id praticien id
     * @param CSejour|null $sejour       sejour
     *
     * @return CConsultation|null
     */
    private function searchConsultation(
        string $dateTime,
        CPatient $patient,
        int $praticien_id = null,
        CSejour $sejour = null
    ): ?CConsultation {
        // Recherche de la consutlation dans le séjour
        $date = CMbDT::date($dateTime);

        $search_min_appointment = '2';
        $search_max_appointment = '1';
        $date_before            = CMbDT::date("- $search_min_appointment DAY", $date);
        $date_after             = CMbDT::date("+ $search_max_appointment DAY", $date);

        $consultation = new CConsultation();
        $where        = [
            "patient_id"        => "= '$patient->_id'",
            "annule"            => "= '0'",
            "plageconsult.date" => "BETWEEN '$date_before' AND '$date_after'",
        ];

        // On va lier le séjour
        if ($sejour) {
            $where["sejour_id"] = "= '$sejour->_id'";
        }

        // Praticien renseigné, on recherche par ce dernier
        if ($praticien_id) {
            $where["plageconsult.chir_id"] = "= '$praticien_id'";
        }

        $leftjoin      = ["plageconsult" => "consultation.plageconsult_id = plageconsult.plageconsult_id"];
        $consultations = $consultation->loadList($where, "plageconsult.date DESC", null, null, $leftjoin);

        if (count($consultations) > 1) {
            return null;
        }
        if ($consultations) {
            return reset($consultations);
        }

        // Recherche d'une consultation qui pourrait correspondre
        unset($where["sejour_id"]);
        $consultations = $consultation->loadList($where, "plageconsult.date DESC", null, null, $leftjoin);
        if (count($consultations) > 1) {
            return null;
        }
        if ($consultations) {
            return reset($consultations);
        }

        return null;
    }
}
