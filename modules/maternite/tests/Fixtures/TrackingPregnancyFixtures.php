<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite\Tests\Fixtures;

use Ox\Core\CModelObjectException;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Patients\CPatient;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;

class TrackingPregnancyFixtures extends Fixtures
{
    public const TAG_PREGNANCY_TRACKING = 'pregnancy_tracking';

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     */
    public function load(): void
    {
        $patiente = $this->generatePatiente();

        $this->generateGrossesse(self::TAG_PREGNANCY_TRACKING, $patiente);
    }

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     */
    private function generatePatiente(): CPatient
    {
        $patient       = CPatient::getSampleObject();
        $patient->sexe = 'f';

        $this->store($patient);

        return $patient;
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    private function generateGrossesse(string $tag, CPatient $patiente): void
    {
        $grossesse                 = CGrossesse::getSampleObject();
        $grossesse->parturiente_id = $patiente->_id;

        $this->store($grossesse, $tag);
    }
}
