<?php
/**
 * @package Mediboard\core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Libraries;

use Ox\Core\CMbException;
use Ox\Core\Libraries\CLibrary;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\Filesystem\Filesystem;

class CLibraryTest extends OxUnitTestCase
{
    public function testUpToDate(): void
    {
        $retour = CLibrary::installAll();
        $this->assertStringStartsWith("Front libraries (js) are up to date", $retour);
    }

    /**
     * @return void
     * @throws CMbException
     * @group schedules
     */
    public function testInstallAdLib(): void
    {
        $root = dirname(__DIR__, 4);
        CLibrary::init();
        $library = reset(CLibrary::$all);
        $fs      = new Filesystem();
        $fs->remove($root . '/lib/' . $library->targetDir);

        $this->assertFalse(CLibrary::checkAll());
        $retour = CLibrary::installAll();
        $this->assertStringStartsWith("Install 1 front libraries (js)", $retour);
    }
}
