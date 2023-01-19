<?php

/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\OpenData\Tests\Unit;

use Ox\Core\CSQLDataSource;
use Ox\Mediboard\OpenData\CCommuneFrance;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CCommuneFranceTest extends OxUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (!CSQLDataSource::get('INSEE', true)) {
            $this->markTestSkipped('Database INSEE is missing');
        }
    }

    public function testLoadByInseeFound(): void
    {
        $commune = new CCommuneFrance();
        $commune->loadByInsee('75056');

        $this->assertEquals('Paris', $commune->commune);
    }
    
    public function testLoadByInseeNotFound(): void
    {
        $commune = new CCommuneFrance();
        $commune->loadByInsee('00000');

        $this->assertNull($commune->_id);
    }
}
