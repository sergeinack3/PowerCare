<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace Ox\Mediboard\Cabinet\Tests\Unit;

use Exception;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\Tests\Fixtures\CabinetFixtures;
use Ox\Tests\OxUnitTestCase;

/**
 * Tests for CConsultation class
 */
class CConsultationTest extends OxUnitTestCase
{
    /**
     * @param CConsultation $consultation
     *
     * @dataProvider plageConsultOwnerProvider
     */
    public function testLoadRefPraticienReturnPlageConsultOwner(CConsultation $consultation): void
    {
        $consultation->loadRefPraticien();
        $this->assertEquals($consultation->_ref_praticien->_id, $consultation->_ref_plageconsult->chir_id);
    }

    /**
     * @throws Exception
     */
    public function plageConsultOwnerProvider(): array
    {
        /** @var CConsultation $consult */
        $consult = $this->getObjectFromFixturesReference(CConsultation::class, CabinetFixtures::TAG_CONSULT_PRAT);
        /** @var CConsultation $consult_rempl */
        $consult_rempl = $this->getObjectFromFixturesReference(
            CConsultation::class,
            CabinetFixtures::TAG_CONSULT_REMPL
        );

        return [
            "consult normale"           => [$consult],
            "consult avec remplacement" => [$consult_rempl],
        ];
    }
}
