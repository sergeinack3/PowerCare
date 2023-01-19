<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Populate\Generators\COperationGenerator;
use Ox\Mediboard\Populate\Generators\CSejourGenerator;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use ReflectionException;

/**
 * Classe de test du service DossierSoinsService
 */
class DossierSoinsServiceTest extends OxUnitTestCase
{
    /**
     * @throws CMbException
     */
    public function testConstructThrowException(): void
    {
        $this->expectException(CMbException::class);
        new DossierSoinsService("-1", CMbDT::date(), null);
    }

    /**
     * @param string $sejour_id
     * @param string $operation_id
     *
     * @throws CMbException
     * @dataProvider getterDataProvider
     */
    public function testGetterSejour(int $sejour_id, ?int $operation_id): void
    {
        $service = new DossierSoinsService($sejour_id, CMbDT::date(), $operation_id);

        $actual_sejour    = $service->getSejour();
        $actual_patient   = $service->getPatient();
        $actual_operation = $service->getOperation();

        $this->assertInstanceOf(CSejour::class, $actual_sejour);
        $this->assertInstanceOf(CPatient::class, $actual_patient);
        $this->assertInstanceOf(COperation::class, $actual_operation);
    }

    /**
     * @dataProvider sejoursDataProvider
     *
     * @param string      $sejour_id
     * @param string      $date
     * @param string|null $operation_id
     *
     * @throws CMbException
     * @throws TestsException
     * @throws ReflectionException
     */
    public function testFormsTabsIsArray(int $sejour_id, string $date, int $operation_id = null): void
    {
        $service = new DossierSoinsService($sejour_id, $date, $operation_id);

        $this->invokePrivateMethod($service, "loadDossierSoinsReferences");

        $this->assertIsArray($service->form_tabs);
    }

    /**
     * @throws CMbException
     * @throws Exception
     * @dataProvider sejoursWithLateObjectifDataProvider
     */
    public function testGetLateObjectifReturnArray(
        int $sejour_id,
        string $date,
        ?int $operation_id,
        int $count_expected
    ): void {
        $service = new DossierSoinsService($sejour_id, $date, $operation_id);

        $actual = $service->getLateObjectifsSoins();
        $this->assertEquals($count_expected, count($actual));
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getterDataProvider(): array
    {
        $data         = [];
        $operations   = [];
        $operations[] = (new COperationGenerator())->generate();
        $operations[] = (new COperationGenerator())->generate();
        $sejour_ids   = CMbArray::pluck($operations, "sejour_id");

        foreach ($sejour_ids as $key => $sejour_id) {
            $data[$key] = [(int) $sejour_id, (int) $operations[$key]->_id];
        }

        return $data;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function sejoursDataProvider(): array
    {
        $data      = [];
        $sejours   = [];
        $sejours[] = (new CSejourGenerator())->generate();
        $sejours[] = (new CSejourGenerator())->generate();
        /** @var CSejour $_sejour */
        foreach ($sejours as $_sejour) {
            $_sejour->loadRefFirstOperation();
            $data_sejour           = [
                (int) $_sejour->_id,
                CMbDT::date(),
                (int) $_sejour->_ref_first_operation->_id,
            ];
            $data["$_sejour->_id"] = $data_sejour;
        }

        return $data;
    }

    /**
     * @throws TestsException
     * @throws Exception
     */
    public function sejoursWithLateObjectifDataProvider(): array
    {
        // Sejour sans objectif de soins
        $sejour0 = (new CSejourGenerator())->generate();

        // Sejour sans operation avec 1 objectif de soins
        $sejour1              = (new CSejourGenerator())->generate();
        $objectif1            = new CObjectifSoin();
        $objectif1->sejour_id = $sejour1->_id;
        $objectif1->libelle   = "Lorem Ipsum";
        $objectif1->user_id   = CMediusers::get()->_id;
        $objectif1->date      = CMbDT::dateTime("-1 day");
        $objectif1->delai     = CMbDT::date("-1 day");
        $objectif1->statut    = "ouvert";
        $objectif1->store();

        // Sejour avec operation avec 1 objectif de soins
        $operation              = (new COperationGenerator())->generate();
        $objectif_op            = new CObjectifSoin();
        $objectif_op->sejour_id = $operation->_ref_sejour->_id;
        $objectif_op->libelle   = "Lorem Ipsum";
        $objectif_op->user_id   = CMediusers::get()->_id;
        $objectif_op->date      = CMbDT::dateTime("-1 day");
        $objectif_op->delai     = CMbDT::date("-1 day");
        $objectif_op->statut    = "ouvert";
        $objectif_op->store();

        // Sejour sans operation avec 2 objectifs de soins
        $sejour2                 = (new CSejourGenerator())->generate();
        $objectif_2_1            = new CObjectifSoin();
        $objectif_2_1->sejour_id = $sejour2->_id;
        $objectif_2_1->libelle   = "Lorem Ipsum";
        $objectif_2_1->user_id   = CMediusers::get()->_id;
        $objectif_2_1->date      = CMbDT::dateTime("-1 day");
        $objectif_2_1->delai     = CMbDT::date("-1 day");
        $objectif_2_1->statut    = "ouvert";
        $objectif_2_1->store();

        $objectif_2_2            = new CObjectifSoin();
        $objectif_2_2->sejour_id = $sejour2->_id;
        $objectif_2_2->libelle   = "Lorem Ipsum";
        $objectif_2_2->user_id   = CMediusers::get()->_id;
        $objectif_2_2->date      = CMbDT::dateTime("-1 day");
        $objectif_2_2->delai     = CMbDT::date("-1 day");
        $objectif_2_2->statut    = "ouvert";
        $objectif_2_2->store();


        return [
            "Sejour sans operation - 0 Objectif"  => [
                (int) $sejour0->_id,
                CMbDT::date(),
                null,
                0,
            ],
            "Sejour sans operation - 1 Objectif"  => [
                (int) $sejour1->_id,
                CMbDT::date(),
                null,
                1,
            ],
            "Sejour sans operation - 2 Objectifs" => [
                (int) $sejour2->_id,
                CMbDT::date(),
                null,
                2,
            ],
            "Sejour avec operation - 1 Objectif"  => [
                (int) $operation->_ref_sejour->_id,
                CMbDT::date(),
                (int) $operation->_id,
                1,
            ],
        ];
    }
}
