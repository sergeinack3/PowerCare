<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Tests\Fixtures\Repository;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CModelObjectException;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * @description Use for test algorithms used in interop to record the Patient
 */
class OperationRepositoryFixtures extends Fixtures implements GroupFixturesInterface
{
    /** @var string */
    public const REF_OPERATION = 'ref_primary_sejour_repository';
    /** @var string */
    public const OPERATION_DATE_ENTREE = "2022-01-20 10:00:00";

    /**
     * @inheritDoc
     * @throws FixturesException|CModelObjectException
     */
    public function load()
    {
        // Patient
        $patient = CPatient::getSampleObject();
        $this->store($patient);

        /** @var CSejour $sejour */
        $sejour = SejourRepositoryFixtures::makePrimarySejour($patient->_id, self::OPERATION_DATE_ENTREE);
        $this->store($sejour);

        /** @var COperation $operation */
        $operation = self::makePrimaryOperation($sejour);
        $this->store($operation, self::REF_OPERATION);
        $this->store($operation);
    }

    /**
     * @inheritDoc
     */
    public static function getGroup(): array
    {
        return ['eai-repository'];
    }

    /**
     * @param CSejour $sejour
     *
     * @return COperation
     * @throws Exception
     */
    public static function makePrimaryOperation(CSejour $sejour, string $datetime = self::OPERATION_DATE_ENTREE): COperation
    {
        $operation                         = new COperation();
        $operation->chir_id                = SejourRepositoryFixtures::getMediusers()->_id;
        $operation->sejour_id              = $sejour->_id;
        $operation->date                   = CMbDT::format($datetime, CMbDT::ISO_DATE);
        $operation->entree_bloc            = CMbDT::dateTime('-10 HOURS', $datetime);
        $operation->sortie_reveil_possible = CMbDT::dateTime('+8 HOURS', $datetime);
        $operation->sortie_reveil_reel     = CMbDT::dateTime('+10 HOURS', $datetime);

        return $operation;
    }
}
