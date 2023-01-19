<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use DateTimeImmutable;
use Exception;
use Ox\Core\CView;
use Ox\Mediboard\Jfse\Domain\InsuranceType\FmfInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\InsuranceTypeService;
use Ox\Mediboard\Jfse\Domain\InsuranceType\MaternityInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\MedicalInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\WorkAccidentInsurance;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Ox\Mediboard\Jfse\Utils;
use Ox\Mediboard\Jfse\ViewModels\CCommonLawAccident;
use Ox\Mediboard\Jfse\ViewModels\InsuranceType\CFmfInsurance;
use Ox\Mediboard\Jfse\ViewModels\InsuranceType\CInsurance;
use Ox\Mediboard\Jfse\ViewModels\InsuranceType\CMaternityInsurance;
use Ox\Mediboard\Jfse\ViewModels\InsuranceType\CMedicalInsurance;
use Ox\Mediboard\Jfse\ViewModels\InsuranceType\CWorkAccidentInsurance;
use PHPUnit\Util\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class InsuranceTypeController extends AbstractController
{
    /** @var string[][] */
    public static $routes = [
        'types/get'                       => [
            'method'  => 'getAll',
            'request' => 'emptyRequest',
        ],
        'invoice/edit'                    => [
            'method'  => 'edit',
            'request' => 'editRequest',
        ],
        'invoice/medical/store'           => [
            'method'  => 'store',
            'request' => 'storeMedicalRequest',
        ],
        'invoice/work_accident/store'     => [
            'method'  => 'store',
            'request' => 'storeWorkAccidentRequest',
        ],
        'invoice/maternity/store'         => [
            'method'  => 'store',
            'request' => 'storeMaternityRequest',
        ],
        'invoice/free_medical_fees/store' => [
            'method'  => 'store',
            'request' => 'storeFMFRequest',
        ],
    ];

    /** @var InsuranceTypeService */
    private $insurance_type_service;

    public function __construct(string $route, InsuranceTypeService $insurance_type_service = null)
    {
        parent::__construct($route);

        $this->insurance_type_service = $insurance_type_service ?? new InsuranceTypeService();
    }

    public static function getRoutePrefix(): string
    {
        return 'insurance';
    }

    /**
     * @route insurance/types/get
     */
    public function getAll(): JsonResponse
    {
        $all_types = $this->insurance_type_service->getAllInsuranceTypes();

        return new JsonResponse($all_types);
    }

    /**
     * @throws Exception
     */
    public function storeMedicalRequest(): Request
    {
        $data = [
            "invoice_id"               => CView::post("invoice_id", "str"),
            "nature_type"              => MedicalInsurance::CODE,
            "code_exoneration_disease" => CView::post("code_exoneration_disease", "num"),
        ];

        return new Request([], $data);
    }

    /**
     * @throws Exception
     */
    public function storeWorkAccidentRequest(): Request
    {
        $organisation_support = CView::post("organisation_support", "str");
        $is_organisation_identical_amo = CView::post("is_organisation_identical_amo", "bool");
        $shipowner_support = CView::post("shipowner_support", "bool");
        $amount_apias = CView::post("amount_apias", "float");
        $number = CView::post("number", "str");

        $data = [
            "invoice_id"                    => CView::post("invoice_id", "str"),
            "nature_type"                   => WorkAccidentInsurance::CODE,
            "date"                          => new DateTimeImmutable(CView::post("date", "date notNull")),
            "has_physical_document"       => (bool)CView::post("has_physical_document", "bool notNull"),
            "number"                        => $number !== '' ?
                (int)$number : null,
            "organisation_support"          => $organisation_support ? $organisation_support : null,
            "is_organisation_identical_amo" => $is_organisation_identical_amo ?
                (bool)$is_organisation_identical_amo : null,
            "organisation_vital"            => (int)CView::post("organisation_vital", "enum list|-1|1|2|3"),
            "shipowner_support"                  => $shipowner_support != '' ? (bool)$shipowner_support : null,
            "amount_apias"                  => $amount_apias ? (float)$amount_apias : null,
        ];

        return new Request([], $data);
    }

    /**
     * @throws Exception
     */
    public function storeMaternityRequest(): Request
    {
        $data = [
            "invoice_id"        => CView::post("invoice_id", "str"),
            "nature_type"       => MaternityInsurance::CODE,
            "date"              => new DateTimeImmutable(CView::post("date", "date notNull")),
            "force_exoneration" => (bool)CView::post("force_exoneration", "bool"),
        ];

        return new Request([], $data);
    }

    /**
     * @throws Exception
     */
    public function storeFMFRequest(): Request
    {
        $data = [
            "invoice_id"              => CView::post("invoice_id", "str"),
            "nature_type"             => FmfInsurance::CODE,
            "supported_fmf_existence" => (bool)CView::post("supported_fmf_existence", "bool notNull"),
            "supported_fmf_expense"   => (float)CView::post("supported_fmf_expense", "float"),
        ];

        return new Request([], $data);
    }

    /**
     * @route insurance/medical/store
     * @route insurance/work_accident/store
     * @route insurance/maternity/store
     * @route insurance/free_medical_fees/store
     * @throws Exception
     */
    public function store(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));
        $is_saved = $this->insurance_type_service->save($request->request->all());

        $data = [];
        if ($is_saved) {
            $data['success'] = true;
            $data['message'] = 'CInsurance-Updated';
        } else {
            $data['error'] = true;
            $data['message'] = 'CInsurance-Not updated';
        }

        return new JsonResponse($data);
    }

    /**
     * @throws Exception
     */
    public function editRequest(): Request
    {
        $invoice_id = CView::post("invoice_id", "str");

        return new Request([], ['invoice_id' => $invoice_id]);
    }

    /**
     * @route insurance/invoice/edit
     */
    public function edit(Request $request): SmartyResponse
    {
        $insurance                          = new CInsurance();
        $insurance->invoice_id              = $request->get('invoice_id');
        $insurance->medical_insurance       = new CMedicalInsurance();
        $insurance->maternity_insurance     = new CMaternityInsurance();
        $insurance->work_accident_insurance = new CWorkAccidentInsurance();
        $insurance->fmf_insurance           = new CFmfInsurance();

        $vars = [
            'insurance'       => $insurance,
            "common_law"      => new CCommonLawAccident(),
            "types"           => $insurance::$types,
            "name_code_types" => $insurance::$name_code_types,
        ];

        return new SmartyResponse('insurance_type/insurance_edit', $vars);
    }
}
