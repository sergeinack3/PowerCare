<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit\Entities;

use Ox\Core\CMbException;
use Ox\Mediboard\Patients\Entities\CConstantTypeBookmark;
use Ox\Tests\OxUnitTestCase;
use Ox\Tests\TestsException;
use ReflectionException;

/**
 * Test class for CConstantTypeBookmark.
 */
class CConstantTypeBookmarkTest extends OxUnitTestCase
{
    /**
     * Test constant type "Fixtures" is not available to be bookmarked.
     *
     * @return void
     * @throws TestsException
     * @throws ReflectionException
     */
    public function testTypeNotAvailable(): void
    {
        $bookmark_constant                = new CConstantTypeBookmark();
        $bookmark_constant->constant_type = 'Fixtures';

        $this->expectExceptionObject(
            new CMbException(
                'CConstantTypeBookmark-Error-This-constant-type-is-not-available: "%s"',
                $bookmark_constant->constant_type
            )
        );
        $this->invokePrivateMethod($bookmark_constant, 'checkTypeAvailable');
    }
}
