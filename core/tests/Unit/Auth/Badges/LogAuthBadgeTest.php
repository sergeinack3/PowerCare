<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Auth\Badges;

use Ox\Core\Auth\Badges\AbstractToggleBadge;
use Ox\Core\Auth\Badges\LogAuthBadge;

class LogAuthBadgeTest extends AbstractBadgeTest
{
    private const METHOD       = 'test_method';
    private const OTHER_METHOD = 'other_test_method';

    public function getClassName(): string
    {
        return LogAuthBadge::class;
    }

    protected function getObject(): AbstractToggleBadge
    {
        $class = $this->getClassName();

        return new $class(self::METHOD);
    }

    public function testMethod(): void
    {
        /** @var LogAuthBadge $badge */
        $badge = $this->getObject();

        $this->assertEquals(self::METHOD, $badge->getMethod());

        $badge->setMethod(self::OTHER_METHOD);
        $this->assertEquals(self::OTHER_METHOD, $badge->getMethod());
    }
}
