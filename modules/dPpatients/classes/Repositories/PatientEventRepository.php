<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Repositories;

use DateTimeImmutable;
use Exception;
use Ox\Core\CPDODataSource;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CEvenementPatient;

/**
 * Repository to fetch CSampleMovie objects.
 */
class PatientEventRepository
{
    private array $where = [];
    /** @var CPDODataSource|CSQLDataSource */
    private $ds;
    private CEvenementPatient $event;

    public function __construct()
    {
        $this->event = new CEvenementPatient();
        $this->ds    = $this->event->getDS();
    }

    /**
     * Count unsigned documents
     * @Param CMediusers[] $user
     *
     * @throws Exception
     */
    public function countReminder($users = []): int
    {
        $this->addWhereReminder(true);

        $this->addWherePraticienIdIn(array_column($users, "_id"));

        return $this->event->countList($this->where);
    }

    private function addWhereReminder(bool $reminder): void
    {
        if (!isset($this->where['evenement_patient.rappel'])) {
            $this->where['evenement_patient.rappel'] = $this->ds->prepare('= ?', $reminder ? '1' : '0');
        }
    }

    private function addWherePraticienId(CMediusers $user): void
    {
        if (!isset($this->where['evenement_patient.praticien_id'])) {
            $this->where['evenement_patient.praticien_id'] = $this->ds->prepare('= ?', $user->_id);
        }
    }

    public function betweenDates(DateTimeImmutable $date_min, DateTimeImmutable $date_max): PatientEventRepository
    {
        $this->where['evenement_patient.date'] = $this->ds->prepareBetween(
            $date_min->format('Y-m-d H:i:s'),
            $date_max->format('Y-m-d H:i:s')
        );

        return $this;
    }

    public function addWherePraticienIdIn(array $users_id): void
    {
        if (!isset($this->where['evenement_patient.praticien_id'])) {
            $this->where['evenement_patient.praticien_id'] = $this->ds->prepareIn($users_id);
        }
    }
}
