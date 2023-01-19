<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Controllers;

use DateTimeImmutable;
use Exception;
use Ox\Core\CView;
use Ox\Mediboard\Jfse\Controllers\InsuranceTypeController;
use Ox\Mediboard\Jfse\Domain\InsuranceType\FmfInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\InsuranceType;
use Ox\Mediboard\Jfse\Domain\InsuranceType\InsuranceTypeService;
use Ox\Mediboard\Jfse\Domain\InsuranceType\MaternityInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\MedicalInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\WorkAccidentInsurance;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class InsuranceTypeControllerTest extends UnitTestJfse
{

    public function testFormSaveRequestShouldReturnAnEmptyRequest(): void
    {
        $_POST = ['invoice_id' => '1'];

        $this->assertEquals(
            new Request([], ['invoice_id' => 1]),
            (new InsuranceTypeController('invoice/edit'))->editRequest()
        );
    }

    public function testSaveMedicalRequest(): void
    {
        $_POST = [
            "invoice_id"               => 1,
            "nature_type"              => MedicalInsurance::CODE,
            "code_exoneration_disease" => "1",
        ];

        $controller = new InsuranceTypeController('invoice/medical/store');

        $expected = [
            "invoice_id"               => 1,
            "nature_type"              => MedicalInsurance::CODE,
            "code_exoneration_disease" => 1,
        ];

        $this->assertEquals(new Request([], $expected), $controller->storeMedicalRequest());
    }

    public function testSaveWorkAccidentRequest(): void
    {
        $_POST = [
            "invoice_id"                    => "1",
            "nature_type"                   => WorkAccidentInsurance::CODE,
            "date"                          => "2020-10-14",
            "has_physical_document"         => "1",
            "number"                        => "1765",
            "organisation_support"          => "9381",
            "is_organisation_identical_amo" => "1",
            "organisation_vital"            => "3",
            "shipowner_support"             => "1",
            "amount_apias"                  => "10.6",
        ];

        $controller = new InsuranceTypeController('invoice/work_accident/store');

        $expected = [
            "invoice_id"                    => '1',
            "nature_type"                   => WorkAccidentInsurance::CODE,
            "date"                          => new DateTimeImmutable("2020-10-14"),
            "has_physical_document"         => true,
            "number"                        => 1765,
            "organisation_support"          => '9381',
            "is_organisation_identical_amo" => true,
            "organisation_vital"            => 3,
            "shipowner_support"             => true,
            "amount_apias"                  => 10.6,
        ];

        $this->assertEquals(new Request([], $expected), $controller->storeWorkAccidentRequest());
    }

    public function testSaveMaternityRequest(): void
    {
        $_POST = [
            "invoice_id"        => "1",
            "nature_type"       => MaternityInsurance::CODE,
            "date"              => "2020-10-14",
            "force_exoneration" => "1",
        ];

        $controller = new InsuranceTypeController('invoice/maternity/store');

        $expected = [
            "invoice_id"        => 1,
            "nature_type"       => MaternityInsurance::CODE,
            "date"              => new DateTimeImmutable("2020-10-14"),
            "force_exoneration" => true,
        ];

        $this->assertEquals(new Request([], $expected), $controller->storeMaternityRequest());
    }

    public function testSaveFMFRequest(): void
    {
        $_POST = [
            "invoice_id"              => "1",
            "nature_type"             => FmfInsurance::CODE,
            "supported_fmf_existence" => "1",
            "supported_fmf_expense"   => "76.9",
        ];

        $controller = new InsuranceTypeController('invoice/medical/store');

        $expected = [
            "invoice_id"              => '1',
            "nature_type"             => FmfInsurance::CODE,
            "supported_fmf_existence" => true,
            "supported_fmf_expense"   => 76.9,
        ];

        $this->assertEquals(new Request([], $expected), $controller->storeFMFRequest());
    }

    public function testGetAll(): void
    {
        $types = [
            InsuranceType::hydrate(['code' => 1, 'label' => 'Libelle 1']),
            InsuranceType::hydrate(['code' => 2, 'label' => 'Libelle 2']),
        ];

        $service = $this->getMockBuilder(InsuranceTypeService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllInsuranceTypes'])
            ->getMock();
        $service->method('getAllInsuranceTypes')->willReturn($types);

        $expected_decoded_data = [
            ['code' => 1, 'label' => 'Libelle 1'],
            ['code' => 2, 'label' => 'Libelle 2'],
        ];

        $this->assertEquals(
            new JsonResponse($expected_decoded_data),
            (new InsuranceTypeController('types/get', $service))->getAll()
        );
    }

    /**
     * Check if the route prefix is a non empty string
     */
    public function testGetRoutePrefix(): void
    {
        $prefix = InsuranceTypeController::getRoutePrefix();
        $this->assertNotEmpty($prefix);
    }
}
