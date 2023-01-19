<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace Ox\Mediboard\Cabinet\Tests\Unit;

use Ox\Core\CMbDT;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Cabinet\PlanningConsultImpressionService;
use Ox\Mediboard\Cabinet\Tests\Fixtures\PlanningConsultImpressionServiceFixtures;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\OxUnitTestCase;

class PlanningConsultImpressionServiceTest extends OxUnitTestCase
{
    public function testCreateContentsWithChirWillReturnPlageConsultAndConsult(): void
    {
        /** @var CPlageconsult $plage_consult */
        $plage_consult = $this->getObjectFromFixturesReference(
            CPlageconsult::class,
            PlanningConsultImpressionServiceFixtures::PLANNING_CONSULT_IMPRESSION_SERVICE_PLAGE_CONSULT
        );

        /** @var CConsultation $consult */
        $consult = $this->getObjectFromFixturesReference(
            CConsultation::class,
            PlanningConsultImpressionServiceFixtures::PLANNING_CONSULT_IMPRESSION_SERVICE_CONSULT
        );

        $chir = CMediusers::findOrFail($plage_consult->chir_id);

        $planning_consult_impression = new PlanningConsultImpressionService(
            CMbDT::date(),
            CMbDT::date(),
            null,
            null,
            null,
            null,
            $chir->_id
        );

        $contents = $planning_consult_impression->getContents();
        $this->assertEquals(2, count($contents));
        $this->assertTrue(array_key_exists("plage_consult", $contents));
        $this->assertTrue(array_key_exists("consults", $contents));

        $contents_plage_consult = $contents["plage_consult"];
        $this->assertEquals(1, count($contents_plage_consult));
        $this->assertEquals($plage_consult->_id, reset($contents_plage_consult)->_id);

        $contents_consult = $contents["consults"];
        $this->assertEquals(1, count($contents_consult));
        $this->assertEquals(1, count($contents_consult[$plage_consult->_id]));
        $this->assertEquals($consult->_id, reset($contents_consult[$plage_consult->_id])->_id);
    }

    public function testCreateContentsWithPlageConsultIdWillReturnPlageConsultAndConsult(): void
    {
        /** @var CPlageconsult $plage_consult */
        $plage_consult = $this->getObjectFromFixturesReference(
            CPlageconsult::class,
            PlanningConsultImpressionServiceFixtures::PLANNING_CONSULT_IMPRESSION_SERVICE_PLAGE_CONSULT
        );

        /** @var CConsultation $consult */
        $consult = $this->getObjectFromFixturesReference(
            CConsultation::class,
            PlanningConsultImpressionServiceFixtures::PLANNING_CONSULT_IMPRESSION_SERVICE_CONSULT
        );

        $planning_consult_impression = new PlanningConsultImpressionService(
            CMbDT::date(),
            CMbDT::date(),
            null,
            $plage_consult->_id
        );

        $contents = $planning_consult_impression->getContents();
        $this->assertEquals(2, count($contents));
        $this->assertTrue(array_key_exists("plage_consult", $contents));
        $this->assertTrue(array_key_exists("consults", $contents));

        $contents_plage_consult = $contents["plage_consult"];
        $this->assertEquals(1, count($contents_plage_consult));
        $this->assertEquals($plage_consult->_id, reset($contents_plage_consult)->_id);

        $contents_consult = $contents["consults"];
        $this->assertEquals(1, count($contents_consult));
        $this->assertEquals(1, count($contents_consult[$plage_consult->_id]));
        $this->assertEquals($consult->_id, reset($contents_consult[$plage_consult->_id])->_id);
    }

    public function testCreateContentsWithLibelleWillReturnPlageConsultAndConsult(): void
    {
        /** @var CPlageconsult $plage_consult */
        $plage_consult = $this->getObjectFromFixturesReference(
            CPlageconsult::class,
            PlanningConsultImpressionServiceFixtures::PLANNING_CONSULT_IMPRESSION_SERVICE_PLAGE_CONSULT
        );

        /** @var CConsultation $consult */
        $consult = $this->getObjectFromFixturesReference(
            CConsultation::class,
            PlanningConsultImpressionServiceFixtures::PLANNING_CONSULT_IMPRESSION_SERVICE_CONSULT
        );

        $planning_consult_impression = new PlanningConsultImpressionService(
            CMbDT::date(),
            CMbDT::date(),
            null,
            null,
            null,
            $plage_consult->libelle,
            $plage_consult->chir_id
        );

        $contents = $planning_consult_impression->getContents();
        $this->assertEquals(2, count($contents));
        $this->assertTrue(array_key_exists("plage_consult", $contents));
        $this->assertTrue(array_key_exists("consults", $contents));

        $contents_plage_consult = $contents["plage_consult"];
        $this->assertEquals(1, count($contents_plage_consult));
        $this->assertEquals($plage_consult->_id, reset($contents_plage_consult)->_id);

        $contents_consult = $contents["consults"];
        $this->assertEquals(1, count($contents_consult));
        $this->assertEquals(1, count($contents_consult[$plage_consult->_id]));
        $this->assertEquals($consult->_id, reset($contents_consult[$plage_consult->_id])->_id);
    }
}
