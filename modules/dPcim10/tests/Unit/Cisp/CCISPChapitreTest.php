<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\dPcim10\Tests\Unit;

use Ox\Mediboard\Cim10\Cisp\CCISP;
use Ox\Mediboard\Cim10\Cisp\CCISPChapitre;
use Ox\Tests\OxUnitTestCase;

class CCISPChapitreTest extends OxUnitTestCase
{
    public function testUpdateFormFields(): void
    {
        $chapitre = new CCISPChapitre();
        $chapitre->lettre = "A";
        $chapitre->description = "une description";
        $chapitre->note = "10";
        $chapitre->updateFormFields();
        $this->assertEquals($chapitre->lettre, $chapitre->_view);
    }

    public function testGetChapitre(): void
    {
        $chapitres = CCISPChapitre::getChapitres();
        $this->assertContainsOnlyInstancesOf(CCISPChapitre::class, $chapitres);
    }

    public function loadRefsCISPSTest(): void
    {
        $chapitre = new CCISPChapitre();
        $chapitre->lettre = "B";
        $refCISPS = $chapitre->loadRefsCISPS();
        $this->assertCount(25, $refCISPS);
        $this->assertContainsOnlyInstancesOf(CCISP::class, $refCISPS);
    }
}
