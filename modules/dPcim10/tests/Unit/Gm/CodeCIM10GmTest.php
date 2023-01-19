<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\dPcim10\Tests\Unit;

use Ox\Mediboard\Cim10\CFavoriCIM10;
use Ox\Mediboard\Cim10\Gm\CCodeCIM10GM;
use Ox\Mediboard\Cim10\Tests\Fixtures\CIM10Fixtures;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class CodeCIM10GmTest extends OxUnitTestCase
{
    public function testCodeCIM10GetCode(): void
    {
        $this->assertEquals("A00", CCodeCIM10GM::getCode(1));
    }

    public function testgetId(): void
    {
        $this->assertEquals(1, CCodeCIM10GM::getId("A00"));
    }

    /**
     * @throws TestsException
     */
    public function testFincCodesWithFavory(): void
    {
        /** @var CMediusers $user */
        $user = $this->getObjectFromFixturesReference(CMediusers::class, CIM10Fixtures::USER_CIM10_WITH_FAVORIS);
        /** @var CFavoriCIM10 $favori */
        $favori = $this->getObjectFromFixturesReference(CFavoriCIM10::class, CIM10Fixtures::FAVORIS_USER_NOT_DELETE);
        $code   = CCodeCIM10GM::get($favori->favoris_code);

        $result = CCodeCIM10GM::findCodes("P00", '', null, null, null, null, null, null, null, $user);
        $expected = ["id" => $code->id, "code" => $code->code];

        $this->assertContains($expected, $result);
        $this->assertEquals($expected, $result[0]);
    }


}
