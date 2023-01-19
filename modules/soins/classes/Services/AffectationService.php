<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins\Services;

use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Service
 */
class AffectationService
{
    /**
     * @param array         $where
     * @param array         $ljoin
     * @param array         $services_ids
     * @param mixed         $service_id
     * @param string        $date
     * @param string        $mode
     *
     * @return array
     * @throws \Exception
     */
    public function loadAffectations(
        array $where,
        array $ljoin,
        array $services_ids,
        $service_id,
        string $date,
        string $mode
    ): array {
        $affectation = new CAffectation();
        $affectations = $affectation->loadList($where, null, null, "affectation.sejour_id", $ljoin, null, null, false);
        // Ajout des affectations des séjours des patients en permission
        $service_perm = new CService();
        foreach ($service_perm->loadList(["service_id" => CSQLDataSource::prepareIn($services_ids, $service_id)]) as $_service_perm) {
            $affectations += loadAffectationsPermissions($_service_perm, $date, $mode);
        }

        return $affectations;
    }

    /**
     * @param array $affectations
     * @param array $sejours
     *
     * @return array|CSejour[]
     * @throws \Exception
     */
    public function updateSejoursFromAffectations(array $affectations, array $sejours): array
    {
        /** @var CSejour[] $sejours */
        $all_sejours = CStoredObject::massLoadFwdRef($affectations, "sejour_id", null, true);
        foreach ($all_sejours as $_new_sejour) {
            if (!isset($sejours[$_new_sejour->_id])) {
                $sejours[$_new_sejour->_id] = $_new_sejour;
            }
        }

        return $sejours;
    }

    /**
     * @param array        $affectations
     * @param CModule|null $hotellerie_active
     * @param string       $date
     * @param string|null  $mode
     */
    public function prepareAffectations(array &$affectations, ?CModule $hotellerie_active, string $date, ?string $mode): void
    {
        /* @var CAffectation[] $affectations */
        foreach ($affectations as $_affectation) {
            $sejour = $_affectation->loadRefSejour(1);
            $affectation_date = $mode === 'day' ? $date : null;
            $sejour->loadRefCurrAffectation($affectation_date, $_affectation->service_id);
            $sejour->_ref_curr_affectation->loadRefLit()->loadCompleteView();
        }

        $this->prepareCleanup($affectations, $hotellerie_active, $date);
    }

    /**
     * @param array        $affectations
     * @param CModule|null $hotellerie_active
     * @param string       $date
     */
    public function prepareCleanup(array &$affectations, ?CModule $hotellerie_active, string $date): void
    {
        if ($hotellerie_active) {
            foreach ($affectations as $_affectation) {
                $cleanup = $_affectation->loadRefLit()->loadLastCleanup($date);
                $cleanup->getColorStatusCleanup(true);
            }
        }
    }
}
