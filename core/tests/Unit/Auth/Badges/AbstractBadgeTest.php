<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Auth\Badges;

use Ox\Core\Auth\Badges\AbstractToggleBadge;
use Ox\Tests\OxUnitTestCase;

abstract class AbstractBadgeTest extends OxUnitTestCase
{
    abstract public function getClassName(): string;

    public function testDefaultValue(): void
    {
        $badge = $this->getObject();

        $this->assertTrue($badge->isEnabled());
    }

    public function testToggle(): void
    {
        $badge = $this->getObject();

        $badge->disable();
        $this->assertFalse($badge->isEnabled());

        $badge->enable();
        $this->assertTrue($badge->isEnabled());
    }

    public function testIsAlwaysResolved(): void
    {
        $badge = $this->getObject();
        $this->assertTrue($badge->isResolved());
    }

    protected function getObject(): AbstractToggleBadge
    {
        $class = $this->getClassName();

        return new $class();
    }
}
