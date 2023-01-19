<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Board\Tests\Unit;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Mediboard\Board\TdbSaisieCodages;
use Ox\Mediboard\Board\Tests\Fixtures\TdbFixtures;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class TdbSaisieCodagesTest extends OxUnitTestCase
{
    public CMediusers $user;

    public array $filters;

    public TdbSaisieCodages $service;

    /**
     * @throws TestsException
     * @throws Exception
     * @pref allow_other_users_board read_right
     */
    protected function setUp(): void
    {
        parent::setUp();
        /** @var CMediusers $user */
        $user = $this->getObjectFromFixturesReference(CMediusers::class, TdbFixtures::REF_TDB_CHIR);

        $this->user    = $user;
        $this->filters = [
            'chir_id'               => $user->_id,
            'begin_date'            => CMbDT::date("-1 week"),
            'end_date'              => CMbDT::date("+1 week"),
            'object_classes'        => ['CConsultation', 'COperation', 'CSejour', 'CSejour-seance'],
            'show_unexported_acts'  => true,
            'objects_without_codes' => true,
            'display_all'           => true,
        ];

        $this->service = new TdbSaisieCodages($this->user, $this->filters);

        $this->service->loadObjetsNonCotes();
    }

    /**
     * @throws Exception
     */
    public function testGetTotalReturnExpected(): void
    {
        $expected = [
            "CConsultation"  => 1,
            "COperation"     => 2,
            "CSejour"        => 2,
            "CSejour-seance" => 1,
        ];
        $this->assertEquals($expected, $this->service->getTotal());
    }

    /**
     * @throws TestsException
     */
    public function testGetSejourContainsRefFromFixtures(): void
    {
        $sejour = $this->getObjectFromFixturesReference(CSejour::class, TdbFixtures::REF_TDB_SEJOUR);

        $this->assertArrayHasKey($sejour->_id, $this->service->getSejours());
    }

    /**
     * @throws TestsException
     */
    public function testGetConsultContainsRefFromFixtures(): void
    {
        $consult = $this->getObjectFromFixturesReference(CConsultation::class, TdbFixtures::REF_TDB_CONSULT);

        $this->assertArrayHasKey($consult->_id, $this->service->getConsultations());
    }

    /**
     * @throws TestsException
     */
    public function testGetInterventionsContainsRefFromFixtures(): void
    {
        $op = $this->getObjectFromFixturesReference(COperation::class, TdbFixtures::REF_TDB_OP);

        $this->assertArrayHasKey($op->_id, $this->service->getInterventions());
    }

    /**
     * @throws TestsException
     */
    public function testGetSeancesContainsRefFromFixtures(): void
    {
        $op = $this->getObjectFromFixturesReference(CSejour::class, TdbFixtures::REF_TDB_SEANCE);

        $this->assertArrayHasKey($op->_id, $this->service->getSeances());
    }

    public function testGetTotalsReturnsExpectedValues(): void
    {
        $this->assertEquals(1, $this->service->getTotalOperationsNonCotees());
        $this->assertEquals(1, $this->service->getTotalConsultationsNonCotees());
        $this->assertEquals(1, $this->service->getTotalSeancesNonCotees());
        $this->assertEquals(1, $this->service->getTotalSejoursNonCotes());
    }

    /**
     * @throws Exception
     * @dataProvider expectedExportedLinesProvider
     */
    public function testExportToCsv(array $sejours, array $consults, array $operations): void
    {
        $this->service->exportToCsv(false);

        $actual = $this->service->getExportedSejours();
        $this->assertEquals($sejours, $actual);

        $actual = $this->service->getExportedConsult();
        $this->assertEquals($consults, $actual);

        $actual = $this->service->getExportedOperation();
        $this->assertEquals($operations, $actual);
    }

    /**
     * @throws TestsException
     * @throws Exception
     */
    public function expectedExportedLinesProvider(): array
    {
        /** @var COperation $op_hp */
        $op_hp = $this->getObjectFromFixturesReference(COperation::class, TdbFixtures::REF_TDB_HP);
        $op_hp->getTemplateClasses();
        /** @var COperation $op */
        $op = $this->getObjectFromFixturesReference(COperation::class, TdbFixtures::REF_TDB_OP);
        $op->getTemplateClasses();
        /** @var CPatient $patient */
        $patient = $this->getObjectFromFixturesReference(CPatient::class, TdbFixtures::REF_PATIENT);
        /** @var CSejour $sejour */
        $sejour = $this->getObjectFromFixturesReference(CSejour::class, TdbFixtures::REF_TDB_SEJOUR);
        /** @var CSejour $seance */
        $seance = $this->getObjectFromFixturesReference(CSejour::class, TdbFixtures::REF_TDB_SEANCE);
        /** @var CConsultation $consult */
        $consult = $this->getObjectFromFixturesReference(CConsultation::class, TdbFixtures::REF_TDB_CONSULT);

        $sejour_exported  = [
            [
                $patient->_view,
                "$patient->_view - $sejour->_view",
                "0acte(s)",
                $sejour->codes_ccam,
                "$sejour->codes_ccam-1-0",
            ],
            [
                $patient->_view,
                "$patient->_view - $seance->_view",
                "0acte(s)",
                $seance->codes_ccam,
                "",
            ],
        ];
        $consult_exported = [
            [
                $patient->_view,
                "Consultation le " . CMbDT::format($consult->_datetime, "%d/%m/%Y"),
                "2acte(s)",
                $consult->codes_ccam,
                "",
            ],
        ];

        $operations_exported = [
            [
                $patient->_view,
                $op_hp->_view,
                "1acte(s)",
                $op_hp->codes_ccam,
                "",
            ],
            [
                $patient->_view,
                $op->_view,
                "0acte(s)",
                $op->codes_ccam,
                "$op->codes_ccam-1-0",
            ],
        ];

        return [[$sejour_exported, $consult_exported, $operations_exported]];
    }
}
