<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Tests\Unit\Repository;

use Ox\Core\CMbDT;
use Ox\Interop\Eai\Repository\ConsultationRepository;
use Ox\Interop\Eai\Repository\Exceptions\ConsultationRepositoryException;
use Ox\Interop\Eai\Tests\Fixtures\Repository\ConsultationRepositoryFixtures;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class ConsultationRepositoryTest extends OxUnitTestCase
{
    /**
     * @return void
     * @throws TestsException
     */
    public function testConsultationWithDate(): void
    {
        /** @var CConsultation $consultation */
        $consultation = $this->getObjectFromFixturesReference(
            CConsultation::class,
            ConsultationRepositoryFixtures::REF_CONSULTATION
        );

        $consultation_found = (new ConsultationRepository(ConsultationRepository::STRATEGY_ONLY_DATE))
            ->setPatient(CPatient::find($consultation->patient_id))
            ->setDateConsultation(ConsultationRepositoryFixtures::CONSULTATION_DATE)
            ->find();

        $this->assertNotNull($consultation_found);
        $this->assertEquals($consultation_found->_id, $consultation->_id);
    }

    public function testConsultationOutOfBound(): void
    {
        /** @var CConsultation $consultation */
        $consultation = $this->getObjectFromFixturesReference(
            CConsultation::class,
            ConsultationRepositoryFixtures::REF_CONSULTATION
        );
        $date         = CMbDT::dateTime('-1 DAY', ConsultationRepositoryFixtures::CONSULTATION_DATE);

        $consultation_found = (new ConsultationRepository(ConsultationRepository::STRATEGY_ONLY_DATE))
            ->setPatient(CPatient::find($consultation->patient_id))
            ->setDateConsultation($date)
            ->find();

        $this->assertNull($consultation_found);
    }

    public function testConsultationWithDateExtended(): void
    {
        /** @var CConsultation $consultation */
        $consultation = $this->getObjectFromFixturesReference(
            CConsultation::class,
            ConsultationRepositoryFixtures::REF_CONSULTATION
        );
        $date         = CMbDT::dateTime('-1 DAY', ConsultationRepositoryFixtures::CONSULTATION_DATE);

        $consultation_found = (new ConsultationRepository(ConsultationRepository::STRATEGY_ONLY_DATE_EXTENDED))
            ->setPatient(CPatient::find($consultation->patient_id))
            ->setDateConsultation($date)
            ->find();

        $this->assertNotNull($consultation_found);
        $this->assertEquals($consultation_found->_id, $consultation->_id);
    }

    public function testConsultationWithBestStrategy(): void
    {
        /** @var CConsultation $consultation */
        $consultation = $this->getObjectFromFixturesReference(
            CConsultation::class,
            ConsultationRepositoryFixtures::REF_CONSULTATION
        );
        $date         = CMbDT::dateTime('-1 DAY', ConsultationRepositoryFixtures::CONSULTATION_DATE);

        $consultation_found = ($repo = new ConsultationRepository(ConsultationRepository::STRATEGY_BEST))
            ->setPatient(CPatient::find($consultation->patient_id))
            ->setDateConsultation($date)
            ->setPraticienId($consultation->loadRefPlageConsult()->chir_id)
            ->setSejour(CSejour::find($consultation->sejour_id))
            ->find();

        $this->assertNotNull($consultation_found);
        $this->assertEquals($consultation_found->_id, $consultation->_id);
    }

    public function testConsultationWithBadSejour(): void
    {
        /** @var CConsultation $consultation */
        $consultation = $this->getObjectFromFixturesReference(
            CConsultation::class,
            ConsultationRepositoryFixtures::REF_CONSULTATION
        );
        $sejour       = new CSejour();
        $sejour->_id  = 9999999;

        $consultation_found = (new ConsultationRepository(ConsultationRepository::STRATEGY_BEST))
            ->setPatient(CPatient::find($consultation->patient_id))
            ->setDateConsultation(ConsultationRepositoryFixtures::CONSULTATION_DATE)
            ->setSejour($sejour)
            ->setPraticienId($consultation->loadRefPraticien())
            ->find();

        $this->assertNull($consultation_found);
    }

    public function testGetOrFind(): void
    {
        /** @var CConsultation $consultation */
        $consultation       = $this->getObjectFromFixturesReference(
            CConsultation::class,
            ConsultationRepositoryFixtures::REF_CONSULTATION
        );
        $consultation_found = (new ConsultationRepository(ConsultationRepository::STRATEGY_BEST))
            ->setConsultationFound($consultation)
            ->getOrFind();

        $this->assertSame($consultation, $consultation_found);
    }

    public function testConsultationWithDivergenceWithPatient(): void
    {
        /** @var CConsultation $consultation */
        $consultation = $this->getObjectFromFixturesReference(
            CConsultation::class,
            ConsultationRepositoryFixtures::REF_CONSULTATION
        );

        $repo = $this->getMockBuilder(ConsultationRepository::class)
            ->onlyMethods(['searchConsultationFromDate'])
            ->getMock();

        $consultation_retrieved             = new CConsultation();
        $consultation_retrieved->_id = 1;
        $consultation_retrieved->patient_id = 99989;
        $repo->method('searchConsultationFromDate')->willReturn($consultation_retrieved);

        $this->expectException(ConsultationRepositoryException::class);
        $this->expectExceptionCode(ConsultationRepositoryException::PATIENT_DIVERGENCE_FOUND);

        $repo->setPatient(CPatient::find($consultation->patient_id))
            ->setDateConsultation(ConsultationRepositoryFixtures::CONSULTATION_DATE)
            ->find();
    }
}
