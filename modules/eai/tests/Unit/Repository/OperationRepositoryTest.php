<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Tests\Unit\Repository;

use Ox\Core\CMbDT;
use Ox\Interop\Eai\Repository\OperationRepository;
use Ox\Interop\Eai\Tests\Fixtures\Repository\OperationRepositoryFixtures;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\OxUnitTestCase;

class OperationRepositoryTest extends OxUnitTestCase
{
    public function testOperationWithDate(): void
    {
        /** @var COperation $operation */
        $operation = $this->getObjectFromFixturesReference(
            COperation::class,
            OperationRepositoryFixtures::REF_OPERATION
        );

        $operation_found = (new OperationRepository(OperationRepository::STRATEGY_ONLY_DATE))
            ->setPatient(CPatient::find($operation->loadRefSejour()->patient_id))
            ->setDateOperation(OperationRepositoryFixtures::OPERATION_DATE_ENTREE)
            ->setPraticienId($operation->chir_id)
            ->find();

        $this->assertNotNull($operation_found);
        $this->assertEquals($operation->_id, $operation_found->_id);
    }

    public function testOperationWithDateExtended(): void
    {
        /** @var COperation $operation */
        $operation = $this->getObjectFromFixturesReference(
            COperation::class,
            OperationRepositoryFixtures::REF_OPERATION
        );
        $date = CMbDT::dateTime('-1 DAYS', OperationRepositoryFixtures::OPERATION_DATE_ENTREE);

        $operation_found = (new OperationRepository(OperationRepository::STRATEGY_ONLY_DATE_EXTENDED))
            ->setPatient(CPatient::find($operation->loadRefSejour()->patient_id))
            ->setDateOperation($date)
            ->setSejour(CSejour::find($operation->sejour_id))
            ->setPraticienId($operation->loadRefPraticien())
            ->find();

        $this->assertNotNull($operation_found);
        $this->assertEquals($operation->_id, $operation_found->_id);
    }

    public function testOperationWithBestStrategy(): void
    {
        /** @var COperation $operation */
        $operation = $this->getObjectFromFixturesReference(
            COperation::class,
            OperationRepositoryFixtures::REF_OPERATION
        );
        $date = CMbDT::dateTime('-1 DAYS', OperationRepositoryFixtures::OPERATION_DATE_ENTREE);

        $operation_found = (new OperationRepository(OperationRepository::STRATEGY_BEST))
            ->setPatient(CPatient::find($operation->loadRefSejour()->patient_id))
            ->setDateOperation($date)
            ->setSejour(CSejour::find($operation->sejour_id))
            ->find();

        $this->assertNotNull($operation_found);
        $this->assertEquals($operation->_id, $operation_found->_id);
    }

    public function testOperationOutOfBound(): void
    {
        /** @var COperation $operation */
        $operation = $this->getObjectFromFixturesReference(
            COperation::class,
            OperationRepositoryFixtures::REF_OPERATION
        );
        $date = CMbDT::dateTime('-1 DAYS', OperationRepositoryFixtures::OPERATION_DATE_ENTREE);

        $operation_found = (new OperationRepository(OperationRepository::STRATEGY_ONLY_DATE))
            ->setPatient(CPatient::find($operation->loadRefSejour()->patient_id))
            ->setDateOperation($date)
            ->find();

        $this->assertNull($operation_found);
    }

    public function testGetOrFind(): void
    {
        /** @var COperation $operation */
        $operation = $this->getObjectFromFixturesReference(
            COperation::class,
            OperationRepositoryFixtures::REF_OPERATION
        );

        $operation_found = (new OperationRepository())
            ->setOperationFound($operation)
            ->getOrFind();

        $this->assertSame($operation, $operation_found);
    }
}
