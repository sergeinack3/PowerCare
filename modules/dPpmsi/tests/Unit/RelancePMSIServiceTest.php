<?php

/**
 * @package Mediboard\Ameli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Pmsi\Tests\Unit;

use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Pmsi\CRelancePMSI;
use Ox\Mediboard\Pmsi\RelancePMSIService;
use Ox\Mediboard\Pmsi\Tests\Fixtures\CRelancePMSIFixtures;
use Ox\Tests\OxUnitTestCase;

class RelancePMSIServiceTest extends OxUnitTestCase
{
    public function testGetRelanceFromUserOrFunctionWithUserReturnRelanceOfTheUser(): void
    {
        /** @var CMediusers $user_1 */
        $user_1 = $this->getObjectFromFixturesReference(CMediusers::class, CRelancePMSIFixtures::RELANCE_PMSI_1);

        /** @var CRelancePMSI $relance_1 */
        $relance_1 = $this->getObjectFromFixturesReference(CRelancePMSI::class, CRelancePMSIFixtures::RELANCE_PMSI_1);

        $relance_pmsi_service = new RelancePMSIService();
        $relances = $relance_pmsi_service->getRelanceFromUserOrFunction("user", $user_1->_id);
        $this->assertEquals(1, count($relances));
        $this->assertEquals($relance_1->_id, $relances[0]->_id);
    }

    public function testGetRelanceFromUserOrFunctionWithSunctionReturnRelanceOfAllTheUserInTheFunction(): void
    {
        /** @var CRelancePMSI $relance_1 */
        $relance_1 = $this->getObjectFromFixturesReference(CRelancePMSI::class, CRelancePMSIFixtures::RELANCE_PMSI_1);

        /** @var CRelancePMSI $relance_2 */
        $relance_2 = $this->getObjectFromFixturesReference(CRelancePMSI::class, CRelancePMSIFixtures::RELANCE_PMSI_2);

        /** @var CFunctions $function */
        $function = $this->getObjectFromFixturesReference(CFunctions::class, CRelancePMSIFixtures::RELANCE_PMSI_FUNCTION);

        $relance_pmsi_service = new RelancePMSIService();
        $relances = $relance_pmsi_service->getRelanceFromUserOrFunction("function", $function->_id);
        $this->assertEquals(2, count($relances));
        $this->assertEquals($relance_1->_id, $relances[0]->_id);
        $this->assertEquals($relance_2->_id, $relances[1]->_id);
    }
}
