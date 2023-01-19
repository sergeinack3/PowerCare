<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Fixtures;

use Ox\Core\CModelObjectException;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;

class CMedecinFixtures extends Fixtures
{
    public const TAG_MEDECIN = 'medecin';

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     */
    public function load(): void
    {
        $medecin = CMedecin::getSampleObject();
        $this->store($medecin, self::TAG_MEDECIN);
    }
}
