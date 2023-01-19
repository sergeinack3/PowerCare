<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Personnel\Tests\Fixtures;

use DateTimeImmutable;
use Exception;
use Ox\Core\CModelObjectException;
use Ox\Mediboard\Personnel\CRemplacement;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;

class CRemplacementFixtures extends Fixtures
{
    public const TAG_REMPLACEMENT = 'remplacement';

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     * @throws Exception
     */
    public function load(): void
    {
        $users = $this->getUsers(2);

        $remplacant = $users[0];
        $remplace   = $users[1];

        $remplacement                = CRemplacement::getSampleObject();
        $remplacement->remplacant_id = $remplacant->_id;
        $remplacement->remplace_id   = $remplace->_id;
        $remplacement->debut         = '2020-01-01 09:00:00';
        $remplacement->fin           = '2020-01-01 18:00:00';

        $this->store($remplacement, self::TAG_REMPLACEMENT);
    }
}
