<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp\Tests\Fixtures;

use Ox\Core\CModelObjectException;
use Ox\Mediboard\SalleOp\CGestePerop;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;

class CGestePeropFixtures extends Fixtures
{
    public const TAG_GESTE = 'geste_perop';

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    public function load(): void
    {
        $geste_perop = CGestePerop::getSampleObject();
        $geste_perop->user_id = $this->getUser()->_id;
        $this->store($geste_perop, self::TAG_GESTE);
    }
}
