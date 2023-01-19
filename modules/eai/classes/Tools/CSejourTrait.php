<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Tools;

use Ox\Core\CMbDT;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * trait CSejourTrait
 * Sejour utilities EAI
 */
trait CSejourTrait
{
    /**
     * Load sejour from NDA
     *
     * @param string $IPP
     * @param string $tag_patient
     *
     * @return CSejour|null
     */
    private function loadSejourFromNDA(string $NDA, string $tag_sejour): ?CSejour
    {
        $NDA = CIdSante400::getMatch(
            "CSejour",
            $tag_sejour,
            $NDA
        );

        // Séjour retrouvé par son NDA
        if (!$NDA->_id) {
            return null;
        }

        $sejour = new CSejour();
        $sejour->load($NDA->object_id);

        return $sejour;
    }

    /**
     * Search admit with document
     *
     * @param String   $dateTime     date
     * @param CPatient $patient      patient
     * @param String   $praticien_id praticien id
     *
     * @return CSejour|null
     */
    private function searchSejour(
        string $dateTime,
        CPatient $patient,
        int $group_id,
        int $praticien_id = null
    ): ?CSejour {
        if (!$dateTime) {
            $dateTime = CMbDT::dateTime();
        }

        $search_min_admit = '2';
        $search_max_admit = '1';
        $date_before      = CMbDT::date("- $search_min_admit DAY", $dateTime);
        $date_after       = CMbDT::date("+ $search_max_admit DAY", $dateTime);

        $where = [
            "patient_id" => "= '$patient->_id'",
            "annule"     => "= '0'",
            "group_id"   => "= '$group_id'",
            "entree"     => "BETWEEN '$date_before' AND '$date_after'",
        ];

        if ($praticien_id) {
            $where["praticien_id"] = "= '$praticien_id'";
        }

        $sejour  = new CSejour();
        $sejours = $sejour->loadList($where);
        if (count($sejours) > 1) {
            return null;
        }

        if ($sejours) {
            return reset($sejours);
        }

        return null;
    }
}
