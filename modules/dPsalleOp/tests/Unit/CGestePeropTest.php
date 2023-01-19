<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp;

use Exception;
use Ox\Mediboard\SalleOp\Tests\Fixtures\CGestePeropFixtures;
use Ox\Tests\OxUnitTestCase;
use Ox\Tests\TestsException;

class CGestePeropTest extends OxUnitTestCase
{
    /** @var CGestePerop */
    protected static $geste_perop;

    /**
     * @inheritDoc
     * @return void
     * @throws TestsException
     */
    protected function setUp(): void
    {
        parent::setUp();

        self::$geste_perop =
            $this->getObjectFromFixturesReference(CGestePerop::class, CGestePeropFixtures::TAG_GESTE);
    }

    /**
     * Test label in the _view variable
     */
    public function testLabelInView(): void
    {
        static::$geste_perop->updateFormFields();

        $this->assertEquals(static::$geste_perop->libelle, static::$geste_perop->_view);
    }

    /**
     * Test to load object by chapter
     * @throws Exception
     */
    public function testLoadGestesByChapitre(): void
    {
        $gestes_chapter = static::$geste_perop->loadGestesByChapitre('user_id', static::$geste_perop->user_id);

        $this->assertArrayHasKey("common-No chapter", $gestes_chapter);
        $this->assertArrayHasKey("common-No category", $gestes_chapter["common-No chapter"]);
        $this->assertNotEmpty($gestes_chapter["common-No chapter"]["common-No category"][0]->_id);
    }
}
