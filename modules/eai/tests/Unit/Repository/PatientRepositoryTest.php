<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Tests\Unit\Repository;

use Exception;
use Ox\Core\Cache;
use Ox\Interop\Eai\Repository\PatientRepository;
use Ox\Interop\Eai\Tests\Fixtures\Repository\PatientRepositoryFixtures;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Tests\OxUnitTestCase;

class PatientRepositoryTest extends OxUnitTestCase
{
    /** @var string */
    private static $ipp;
    /** @var string */
    private static $tag_ipp;
    /** @var CPatient */
    private static $patient_ipp;

    /** @var string */
    private static $ins;

    /** @var CPatient */
    private static $patient_ins;

    protected function setUp(): void
    {
        parent::setUp();

        $cache = new Cache('CPatient.getTagIPP', [CGroups::loadCurrent()->_id], Cache::INNER);
        $cache->put("tag_ipp");

        if (!self::$patient_ins || !self::$patient_ipp) {
            $this->initialize();
        }
    }

    /**
     * @return void
     * @throws \Ox\Components\Cache\Exceptions\CouldNotGetCache
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $cache = new Cache('CPatient.getTagIPP', [CGroups::loadCurrent()->_id], Cache::INNER);
        $cache->rem();
    }

    /**
     * @return void
     * @throws Exception
     */
    private function initialize(): void
    {
        $this->initiliazePatientIPP();

        $this->initiliazePatientINS();
    }

    /**
     * @return CPatient
     * @throws Exception
     */
    private function initiliazePatientINS(): CPatient
    {
        /** @var CPatient $patient */
        $patient     = $this->getObjectFromFixturesReference(CPatient::class, PatientRepositoryFixtures::PATIENT_WITH_INS);
        $patient_ins = $patient->loadRefPatientINSNIR();
        if (!$patient_ins || !$patient_ins->_id) {
            $this->fail('patient without INS');
        }

        self::$ins = $patient_ins->ins_nir;

        return self::$patient_ins = $patient;
    }

    /**
     * @return CPatient
     * @throws Exception
     */
    private function initiliazePatientIPP(): CPatient
    {
        // Patient IPP
        /** @var CPatient $patient_ipp */
        $patient_ipp   = $this->getObjectFromFixturesReference(
            CPatient::class,
            PatientRepositoryFixtures::PATIENT_WITH_IPP
        );
        self::$tag_ipp = CPatient::getTagIPP();
        if (!$IPP = $patient_ipp->loadIPP()) {
            $this->fail('patient without IPP');
        }
        self::$ipp = $IPP;

        return self::$patient_ipp = $patient_ipp;
    }

    /**
     * @config eai use_domain 0
     * @config dPpatients CPatient tag_ipp tag_ipp
     *
     * @return void
     */
    public function testWithBadStrategy(): void
    {
        $this->expectException(Exception::class);

        (new PatientRepository('bad_strategy'));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testPatientNotFound(): void
    {
        foreach (PatientRepository::STRATEGIES as $strategy) {
            $this->assertNull((new PatientRepository($strategy))->find());
        }
    }

    public function providerStrategiesIPP(): array
    {
        return [
            'IPP'     => [PatientRepository::STRATEGY_IPP],
            'INS_IPP' => [PatientRepository::STRATEGY_INS_IPP],
            'BEST'    => [PatientRepository::STRATEGY_BEST],
        ];
    }

    /**
     * @dataProvider providerStrategiesIPP
     * @config       eai use_domain 0
     * @config       dPpatients CPatient tag_ipp tag_ipp
     * @throws Exception
     */
    public function testStrategyIPP(string $strategy): void
    {
        $patient_retrieved = (new PatientRepository($strategy))
            ->withIPP(self::$ipp, self::$tag_ipp)
            ->find();

        if ($patient_retrieved === null) {
            $this->fail('Patient not found with IPP');
        }

        $this->assertEquals(self::$patient_ipp->_id, $patient_retrieved->_id);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testStrategyWithFallbackIPP(): void
    {
        $patient_retrieved = (new PatientRepository(PatientRepository::STRATEGY_INS_IPP))
            ->withINS("ins_which_dont_exist")
            ->withIPP(self::$ipp, self::$tag_ipp)
            ->find();

        if ($patient_retrieved === null) {
            $this->fail('Patient not found with INS / IPP');
        }

        $this->assertEquals(self::$patient_ipp->_id, $patient_retrieved->_id);
    }

    public function providerStrategiesINS(): array
    {
        return [
            'INS'     => [PatientRepository::STRATEGY_INS],
            'INS_IPP' => [PatientRepository::STRATEGY_INS_IPP],
            'BEST'    => [PatientRepository::STRATEGY_BEST],
        ];
    }

    /**
     * @dataProvider providerStrategiesINS
     * @throws Exception
     */
    public function testStrategyINS(string $strategy): void
    {
        $patient_retrieved = (new PatientRepository($strategy))
            ->withINS(self::$ins)
            ->find();

        if ($patient_retrieved === null) {
            $this->fail('Patient not found with INS / IPP');
        }

        $this->assertEquals(self::$patient_ins->_id, $patient_retrieved->_id);
    }

    /**
     * @config dPpatients CPatient function_distinct 0
     *
     * @return void
     * @throws Exception
     */
    public function testStrategyPatientTraits(): void
    {
        $patient            = new CPatient();
        $patient->nom       = PatientRepositoryFixtures::TRAIT_FAMILY;
        $patient->prenom    = PatientRepositoryFixtures::TRAIT_GIVEN;
        $patient->naissance = PatientRepositoryFixtures::TRAIT_BIRTH_DATE;

        $patient_retrieved = (new PatientRepository(PatientRepository::STRATEGY_PATIENT_TRAITS))
            ->withPatientSearched($patient, $patient->group_id)
            ->find();

        if ($patient_retrieved === null) {
            $this->fail('Patient not found with this traits');
        }

        $expected_patient = $this->getObjectFromFixturesReference(
            CPatient::class,
            PatientRepositoryFixtures::PATIENT_WITH_NO_IDENTIFIERS
        );
        $this->assertEquals($expected_patient->_id, $patient_retrieved->_id);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testStrategyResourceId(): void
    {
        $expected_patient = $this->getObjectFromFixturesReference(
            CPatient::class,
            PatientRepositoryFixtures::PATIENT_WITH_NO_IDENTIFIERS
        );

        $patient_retrieved = (new PatientRepository(PatientRepository::STRATEGY_RESOURCE_ID))
            ->withResourceId($expected_patient->_id)
            ->find();

        if ($patient_retrieved === null) {
            $this->fail('Patient not found with this id');
        }

        $this->assertEquals($expected_patient->_id, $patient_retrieved->_id);
    }

    public function testStrategyBest(): void
    {
        $patient_locator = $this->getMockBuilder(PatientRepository::class)
            ->setConstructorArgs([PatientRepository::STRATEGY_BEST])
            ->onlyMethods(['findByINS', 'findByIPP', 'findByTraits', 'findByResourceId'])
            ->getMock();

        $patient_locator->expects($this->exactly(1))->method('findByINS');
        $patient_locator->expects($this->exactly(1))->method('findByIPP');
        $patient_locator->expects($this->exactly(1))->method('findByTraits');
        $patient_locator->expects($this->exactly(1))->method('findByResourceId');

        $patient_locator->find();
    }
}
