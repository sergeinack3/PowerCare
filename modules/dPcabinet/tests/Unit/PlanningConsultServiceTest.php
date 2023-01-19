<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace Ox\Mediboard\Cabinet\Tests\Unit;

use Exception;
use Ox\Core\CMbDT;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Cabinet\PlanningConsultService;
use Ox\Mediboard\Cabinet\Tests\Fixtures\PlanningConsultServiceFixtures;
use Ox\Mediboard\Personnel\CPlageConge;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Tests\OxUnitTestCase;

class PlanningConsultServiceTest extends OxUnitTestCase
{
    /**
     * @throws Exception
     * @pref showIntervPlanning 1
     */
    public function testCreateContentsByDateWithoutAnnulee(): void
    {
        /** @var CPlageOp $plage_op */
        $plage_op = $this->getObjectFromFixturesReference(
            CPlageOp::class,
            PlanningConsultServiceFixtures::PLANNING_CONSULT_SERVICE_PLAGE_OPERATOIRE
        );

        /** @var COperation $interv */
        $interv = $this->getObjectFromFixturesReference(
            COperation::class,
            PlanningConsultServiceFixtures::PLANNING_CONSULT_SERVICE_INTERV_HORS_PLAGE
        );

        /** @var CPlageConge $plage_conges */
        $plage_conges = $this->getObjectFromFixturesReference(
            CPlageConge::class,
            PlanningConsultServiceFixtures::PLANNING_CONSULT_SERVICE_PLAGE_CONGES
        );

        /** @var CPlageconsult $plage_consult */
        $plage_consult = $this->getObjectFromFixturesReference(
            CPlageconsult::class,
            PlanningConsultServiceFixtures::PLANNING_CONSULT_SERVICE_PLAGE_CONSULT
        );

        /** @var CConsultation $consult */
        $consult = $this->getObjectFromFixturesReference(
            CConsultation::class,
            PlanningConsultServiceFixtures::PLANNING_CONSULT_SERVICE_CONSULT
        );

        /** @var CConsultation $consult */
        $consult_acte = $this->getObjectFromFixturesReference(
            CConsultation::class,
            PlanningConsultServiceFixtures::PLANNING_CONSULT_SERVICE_CONSULT_ACTE
        );

        $planning_consult_service = new PlanningConsultService(
            CMbDT::date("- 3 DAYS"),
            CMbDT::date("+3 DAYS"),
            $plage_op->chir_id,
            32,
            null,
            null,
            true
        );

        $contents = $planning_consult_service->getContentsByDate();

        $this->assertEquals(7, count($contents));
        $this->assertTrue(array_key_exists(CMbDT::date("-3 DAYS"), $contents));
        $this->assertTrue(array_key_exists(CMbDT::date("-2 DAYS"), $contents));
        $this->assertTrue(array_key_exists(CMbDT::date("-1 DAYS"), $contents));
        $this->assertTrue(array_key_exists(CMbDT::date(), $contents));
        $this->assertTrue(array_key_exists(CMbDT::date("+1 DAYS"), $contents));
        $this->assertTrue(array_key_exists(CMbDT::date("+2 DAYS"), $contents));
        $this->assertTrue(array_key_exists(CMbDT::date("+3 DAYS"), $contents));

        $contents_plage_op = $contents[CMbDT::date()]["plage_op"];
        $this->assertEquals(1, count($contents_plage_op));
        $this->assertEquals($plage_op->_id, reset($contents_plage_op)->_id);

        $contents_interv_hors_plage = $contents[CMbDT::date()]["interv_hors_plage"];
        $this->assertEquals(1, count($contents_plage_op));
        $this->assertEquals($interv->_id, reset($contents_interv_hors_plage)->_id);

        $contents_plage_conges = $contents[CMbDT::date("+1 DAYS")]["conges"];
        $this->assertEquals(1, count($contents_plage_conges));
        $this->assertEquals($plage_conges->_id, reset($contents_plage_conges)->_id);

        $contents_plage_conges = $contents[CMbDT::date("+2 DAYS")]["conges"];
        $this->assertEquals(1, count($contents_plage_conges));
        $this->assertEquals($plage_conges->_id, reset($contents_plage_conges)->_id);

        $contents_plage_consult = $contents[CMbDT::date()]["plage_consult"];
        $this->assertEquals(1, count($contents_plage_consult));
        $this->assertEquals($plage_consult->_id, reset($contents_plage_consult)->_id);

        $contents_consult = $contents[CMbDT::date()]["consults"];
        $this->assertEquals(2, count($contents_consult));
        foreach ($contents_consult as $_consult) {
            $this->assertTrue(in_array($_consult->_id, [$consult->_id, $consult_acte->_id]));
        }
    }

    /**
     * @throws Exception
     * @pref showIntervPlanning 1
     */
    public function testCreateContentsByDateWithConsultAnnuleeWillReturnAllConsult(): void
    {
        /** @var CPlageOp $plage_op */
        $plage_op = $this->getObjectFromFixturesReference(
            CPlageOp::class,
            PlanningConsultServiceFixtures::PLANNING_CONSULT_SERVICE_PLAGE_OPERATOIRE
        );

        /** @var CConsultation $consult */
        $consult = $this->getObjectFromFixturesReference(
            CConsultation::class,
            PlanningConsultServiceFixtures::PLANNING_CONSULT_SERVICE_CONSULT
        );

        /** @var CConsultation $consult */
        $consult_acte = $this->getObjectFromFixturesReference(
            CConsultation::class,
            PlanningConsultServiceFixtures::PLANNING_CONSULT_SERVICE_CONSULT_ACTE
        );

        $consult->annule = 1;
        $this->storeOrFailed($consult);

        $planning_consult_service = new PlanningConsultService(
            CMbDT::date("- 3 DAYS"),
            CMbDT::date("+3 DAYS"),
            $plage_op->chir_id,
            32,
            null,
            null,
            1
        );

        $contents_consult = $planning_consult_service->getContentsByDate()[CMbDT::date()]["consults"];
        $this->assertEquals(2, count($contents_consult));
        foreach ($contents_consult as $_consult) {
            $this->assertTrue(in_array($_consult->_id, [$consult->_id, $consult_acte->_id]));
        }
    }

    /**
     * @throws Exception
     * @pref showIntervPlanning 1
     */
    public function testCreateContentsByDateWithFactureWillReturnConsultWithFacture(): void
    {
        /** @var CPlageOp $plage_op */
        $plage_op = $this->getObjectFromFixturesReference(
            CPlageOp::class,
            PlanningConsultServiceFixtures::PLANNING_CONSULT_SERVICE_PLAGE_OPERATOIRE
        );

        /** @var CConsultation $consult */
        $consult = $this->getObjectFromFixturesReference(
            CConsultation::class,
            PlanningConsultServiceFixtures::PLANNING_CONSULT_SERVICE_CONSULT
        );

        $consult->annule  = 0;
        $consult->facture = 1;
        $this->storeOrFailed($consult);

        $planning_consult_service = new PlanningConsultService(
            CMbDT::date("- 3 DAYS"),
            CMbDT::date("+3 DAYS"),
            $plage_op->chir_id,
            32,
            1,
        );

        $contents_consult = $planning_consult_service->getContentsByDate()[CMbDT::date()]["consults"];
        $this->assertEquals(1, count($contents_consult));
        $this->assertEquals($consult->_id, reset($contents_consult)->_id);
    }

    /**
     * @throws Exception
     * @pref showIntervPlanning 1
     */
    public function testCreateContentsByDateWithActeWillReturnConsultWithActe(): void
    {
        /** @var CPlageOp $plage_op */
        $plage_op = $this->getObjectFromFixturesReference(
            CPlageOp::class,
            PlanningConsultServiceFixtures::PLANNING_CONSULT_SERVICE_PLAGE_OPERATOIRE
        );

        /** @var CConsultation $consult */
        $consult_acte = $this->getObjectFromFixturesReference(
            CConsultation::class,
            PlanningConsultServiceFixtures::PLANNING_CONSULT_SERVICE_CONSULT_ACTE
        );

        $planning_consult_service = new PlanningConsultService(
            CMbDT::date("- 3 DAYS"),
            CMbDT::date("+3 DAYS"),
            $plage_op->chir_id,
            32,
            null,
            true
        );

        $contents_consult = $planning_consult_service->getContentsByDate()[CMbDT::date()]["consults"];
        $this->assertEquals(1, count($contents_consult));
        $this->assertEquals($consult_acte->_id, reset($contents_consult)->_id);
    }

    /**
     * @throws Exception
     * @pref showIntervPlanning 1
     */
    public function testCreateContentsByDateWithoutActeWillReturnConsultWithoutActe(): void
    {
        /** @var CPlageOp $plage_op */
        $plage_op = $this->getObjectFromFixturesReference(
            CPlageOp::class,
            PlanningConsultServiceFixtures::PLANNING_CONSULT_SERVICE_PLAGE_OPERATOIRE
        );

        /** @var CConsultation $consult */
        $consult = $this->getObjectFromFixturesReference(
            CConsultation::class,
            PlanningConsultServiceFixtures::PLANNING_CONSULT_SERVICE_CONSULT
        );

        $planning_consult_service = new PlanningConsultService(
            CMbDT::date("- 3 DAYS"),
            CMbDT::date("+3 DAYS"),
            $plage_op->chir_id,
            32,
            null,
            0
        );

        $contents_consult = $planning_consult_service->getContentsByDate()[CMbDT::date()]["consults"];
        $this->assertEquals(1, count($contents_consult));
        $this->assertEquals($consult->_id, reset($contents_consult)->_id);
    }

    /**
     * @throws Exception
     * @pref showIntervPlanning 1
     */
    public function testCreateContentsWithPlageCOnsultWithoutSlot(): void
    {
        /** @var CPlageconsult $plage_consult */
        $plage_consult = $this->getObjectFromFixturesReference(
            CPlageconsult::class,
            PlanningConsultServiceFixtures::PLANNING_CONSULT_SERVICE_PLAGE_CONSULT_WITHOUT_SLOT
        );

        $slots = $plage_consult->loadRefsSlots();
        foreach ($slots as $_slot) {
            $this->deleteOrFailed($_slot);
        }
        $plage_consult->date = "2020-12-31";
        $this->storeOrFailed($plage_consult);

        $consult                  = new CConsultation();
        $consult->plageconsult_id = $plage_consult->_id;
        $consult->heure           = "10:00:00";
        $consult->owner_id        = $plage_consult->chir_id;
        $consult->chrono          = 8;
        $this->storeOrFailed($consult);

        $planning_consult_service = new PlanningConsultService(
            CMbDT::date("2020-12-31"),
            CMbDT::date("2020-12-31"),
            $plage_consult->chir_id,
        );

        $contents = $planning_consult_service->getContentsByDate();

        $this->assertEquals(1, count($contents));
        $this->assertTrue(array_key_exists(CMbDT::date("2020-12-31"), $contents));

        $contents_plage_consult = $contents[CMbDT::date("2020-12-31")]["plage_consult"];
        $this->assertEquals(1, count($contents_plage_consult));
        $this->assertEquals($plage_consult->_id, reset($contents_plage_consult)->_id);
        $this->assertNotNull(reset($contents_plage_consult)->_ref_slots);

        $contents_consult = $contents[CMbDT::date("2020-12-31")]["consults"];
        $this->assertEquals(1, count($contents_consult));
        $this->assertEquals($consult->_id, reset($contents_consult)->_id);
    }
}
