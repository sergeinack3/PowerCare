<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\dPcim10\Tests\Unit;

use Ox\Mediboard\Cim10\Cisp\CCISPProcedure;
use Ox\Tests\OxUnitTestCase;

class CCISPProcedureTest extends OxUnitTestCase
{
    public function testUpdateFormFields(): void
    {
        $procedure              = new CCISPProcedure();
        $procedure->identifiant = "abc1234";
        $procedure->description = "description de la procédure";
        $procedure->updateFormFields();
        $this->assertEquals($procedure->description, $procedure->_view);
        $this->assertEquals("bc1234", $procedure->_indice);
    }

    public function testGetProcedure(): void
    {
        $procedures = CCISPProcedure::getProcedures();
        $this->assertContainsOnlyInstancesOf(CCISPProcedure::class, $procedures);
    }
}

