<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\dPcim10\Tests\Unit;

use Ox\Mediboard\Cim10\Gm\CNoteCIM10GM;
use Ox\Mediboard\Cim10\Gm\CReferenceCIM10GM;
use Ox\Tests\OxUnitTestCase;

class ReferenceGmTest extends OxUnitTestCase
{
    public function testReferenceCIM10Get(): void
    {
        $expected            = new CReferenceCIM10GM();
        $expected->id        = 1;
        $expected->note_id   = 5;
        $expected->code_id   = 15590;
        $expected->code_type = "code";
        $expected->text      = "Z22.-";
        $expected->usage     = null;
        $this->assertEquals($expected, CReferenceCIM10GM::get(1));
    }

    public function testReferenceCIM10GetFor(): void
    {
        $expected            = new CReferenceCIM10GM();
        $expected->id        = 1;
        $expected->note_id   = 5;
        $expected->code_id   = 15590;
        $expected->code_type = "code";
        $expected->text      = "Z22.-";
        $expected->usage     = null;
        $note = new CNoteCIM10GM(["id" => 5]);
        $this->assertEquals([$expected], CReferenceCIM10GM::getFor($note));
    }
    public function testReferenceCIM10GetNull(): void
    {
        $this->assertEquals(null, CReferenceCIM10GM::get(-1));
    }

}
