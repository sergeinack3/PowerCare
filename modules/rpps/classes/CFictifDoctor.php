<?php

/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps;

use Ox\Core\CStoredObject;
use Ox\Mediboard\Patients\CMedecin;

/**
 * Description
 */
class CFictifDoctor
{
    /**
     * @return CStoredObject[]
     * @throws \Exception
     */
    public function getFictifDoctors(?int $start = 0, ?int $step = 20): array {

        $medecin = new CMedecin();
        $medecin->medecin_fictif = 1;
        $medecins = $medecin->loadMatchingListEsc(null, "$start, $step");

        return $medecins;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function countFictifDoctors(?int $start = 0, ?int $step = 20): int {

        $medecin = new CMedecin();
        $medecin->medecin_fictif = 1;

        return $medecin->countMatchingList();
    }
}
