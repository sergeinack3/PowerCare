<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\dPcim10\Tests\Unit;

use Ox\Core\CMbModelNotFoundException;
use Ox\Mediboard\Cim10\Tests\Fixtures\CIM10Fixtures;
use Ox\Mediboard\Cim10\CFavoriCIM10;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class CFavoriCIM10Test extends OxUnitTestCase
{
    /**
     * @throws TestsException
     * @throws CMbModelNotFoundException
     */
    public function testGetFromCode(): void
    {
        /** @var CFavoriCIM10 $expected */
        $expected = $this->getObjectFromFixturesReference(CFavoriCIM10::class, CIM10Fixtures::FAVORIS_USER);
        $user     = CMediusers::findOrFail($expected->favoris_user);
        $result   = CFavoriCIM10::getFromCode("P00", $user);
        $this->assertObjectEquals($expected, $result);
    }

    public function testFindCodes(): void
    {
        /** @var CFavoriCIM10 $expected */
        $expected = $this->getObjectFromFixturesReference(CFavoriCIM10::class, CIM10Fixtures::FAVORIS_USER);
        $user     = CMediusers::findOrFail($expected->favoris_user);
        $result   = CFavoriCIM10::findCodes($user);
        $this->assertCount(1, $result);
        $this->assertEquals($result[0]->code, 'P00');
    }

    /**
     * @throws TestsException
     * @throws CMbModelNotFoundException
     */
    public function testDeleteFavoris(): void
    {
        /** @var CFavoriCIM10 $expected */
        $favoris = $this->getObjectFromFixturesReference(CFavoriCIM10::class, CIM10Fixtures::FAVORIS_USER);
        $user    = CMediusers::findOrFail($favoris->favoris_user);
        $favoris->delete();
        $this->assertEquals([], CFavoriCIM10::getListFavoris($user));
    }
}
