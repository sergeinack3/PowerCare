<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use DateTime;
use DateTimeImmutable;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Jfse\DataModels\CJfseInvoice;
use Ox\Mediboard\Jfse\Domain\Formula\Formula;
use Ox\Mediboard\Jfse\Domain\Invoicing\ComplementaryHealthInsurance;
use Ox\Mediboard\Jfse\Domain\Invoicing\Invoice;
use Ox\Mediboard\Jfse\Domain\Invoicing\InvoicingService;
use Ox\Mediboard\Jfse\Domain\Vital\AdditionalHealthInsurance;
use Ox\Mediboard\Jfse\Domain\Vital\AmoServicePeriod;
use Ox\Mediboard\Jfse\Domain\Vital\HealthInsurance;
use Ox\Mediboard\Jfse\Domain\Vital\Period;
use Ox\Mediboard\Jfse\Mappers\InvoicingMapper;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Ox\Mediboard\Jfse\Utils;
use Ox\Mediboard\Jfse\ViewModels\Invoicing\CJfseInvoiceView;
use Ox\Mediboard\Jfse\ViewModels\VitalCard\CCardHealthInsurance;
use Ox\Mediboard\Jfse\ViewModels\VitalCard\CPeriod;
use Smarty;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ThirdPartyPaymentController extends AbstractController
{
    /** @var string[][] */
    public static $routes = [
        'edit'                            => [
            'method' => 'displayThirdPartyPayment',
        ],
        'amoService/set'                  => [
            'method' => 'setAmoService'
        ],
        'attackVictim/set'                => [
            'method' => 'setAttackVictim',
        ],
        'c2s/set'                         => [
            'method' => 'setC2S',
        ],
        'complementaryHealthOrganism/set' => [
            'method' => 'setComplementaryHealthOrganism',
        ],
        'healthInsurance/set'             => [
            'method' => 'setHealthInsurance',
        ],
        'healthInsurance/vitalCard'             => [
            'method' => 'setHealthInsuranceFromVitalCard',
        ],
        'additionalHealthInsurance/set'   => [
            'method' => 'setAdditionalHealthInsurance'
        ],
        'additionalHealthInsurance/vitalCard'   => [
            'method' => 'setAdditionalHealthInsuranceFromVitalCard'
        ],
        'acs/set'                         => [
            'method' => 'setACS',
        ],
        'acs/assistant'                   => [
            'method' => 'getAcsAssistant',
        ],
        'convention/select'               => [
            'method' => 'selectConvention',
        ],
        'conventions/view'                => [
            'method' => 'showConventionsList'
        ],
        'formula/select'                  => [
            'method' => 'selectFormula',
        ],
        'formulas/view'                   => [
            'method' => 'showFormulasList'
        ],
    ];

    public static function getRoutePrefix(): string
    {
        return 'thirdPartyPayment';
    }

    public function displayThirdPartyPaymentRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([
            'invoice_id' => CView::post('invoice_id', 'str'),
            'selected_tp_amc' => CView::post('selected_tp_amc', 'enum list|0|1|2')
        ]);
    }

    public function displayThirdPartyPayment(Request $request): SmartyResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));

        $service = new InvoicingService();
        $invoice = CJfseInvoiceView::getFromEntity(
            $service->getInvoice($request->get('invoice_id'))
        );

        if ($request->get('selected_tp_amc') != '') {
            $invoice->complementary_health_insurance->third_party_amc = $request->get('selected_tp_amc');
        }

        return new SmartyResponse('invoicing/third_party_payment_edit', [
            'invoice'              => $invoice,
            'complementary'        => $invoice->complementary_health_insurance,
            'sts_referral_codes'   => $service->getListStsReferralCodes(),
            'treatment_indicators' => $service->getListTreatmentIndicators(),
            'amo_services'         => $service->getListAmoServices($invoice->id)
        ]);
    }

    public function setAttackVictimRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([], ['invoice_id' => CView::post('invoice_id', 'str notNull')]);
    }

    public function setAttackVictim(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));

        $service       = new InvoicingService();
        $complementary = ComplementaryHealthInsurance::hydrate(
            [
                'third_party_amo' => true,
                'third_party_amc' => 0,
                'attack_victim'   => true,
            ]
        );

        $service->setThirdPartyPayment($request->get('invoice_id'), $complementary);

        return new JsonResponse(
            [
                'success' => true,
                'message' => 'CComplementaryHealthInsurance-msg-modified',
            ]
        );
    }

    public function setC2SRequest(): Request
    {
        CCanDo::checkEdit();

        return new request([], ['invoice_id' => CView::post('invoice_id', 'str notNull')]);
    }

    public function setC2S(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));
        $service       = new InvoicingService();
        $complementary = ComplementaryHealthInsurance::hydrate(
            [
                'third_party_amo'  => true,
                'third_party_amc'  => 1,
                'attack_victim'    => false,
                'health_insurance' => HealthInsurance::hydrate(
                    [
                        'id'                              => '88888888',
                        'effective_guarantees'            => '',
                        'treatment_indicator'             => '',
                        'associated_services_type'        => '',
                        'associated_services'             => '',
                        'referral_sts_code'               => '',
                        'health_insurance_periods_rights' => new Period(),
                        'paper_mode'                      => true,
                    ]
                ),
            ]
        );

        $service->setThirdPartyPayment($request->get('invoice_id'), $complementary);

        return new JsonResponse(
            [
                'success' => true,
                'message' => 'CComplementaryHealthInsurance-msg-modified',
            ]
        );
    }

    public function setHealthInsuranceRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([], [
            'invoice_id'               => CView::post('invoice_id', 'str notNull'),
            'insurance_id'             => CView::post('health_insurance_id', 'str notNull'),
            'begin_date'               => CView::post('health_insurance_begin_date', 'date'),
            'end_date'                 => CView::post('health_insurance_end_date', 'date'),
            'pec'                      => CView::post('health_insurance_pec', 'str'),
            'treatment_indicator'      => CView::post('health_insurance_treatment_indicator', 'str'),
            'referral_sts_code'        => CView::post('health_insurance_referral_sts_code', 'str'),
            'effective_guarantees'     => CView::post('health_insurance_effective_guarantees', 'str'),
            'contract_type'            => CView::post('health_insurance_contract_type', 'num'),
            'associated_services'      => CView::post('health_insurance_associated_services', 'str'),
            'associated_services_type' => CView::post('health_insurance_associated_services_type', 'str'),
        ]);
    }

    public function setHealthInsurance(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));

        $complementary = ComplementaryHealthInsurance::hydrate(
            [
                'third_party_amo'  => true,
                'third_party_amc'  => 1,
                'attack_victim'    => false,
                'health_insurance' => HealthInsurance::hydrate([
                    'id'                              => $request->get('insurance_id'),
                    'effective_guarantees'            => $request->get('health_insurance_effective_guarantees'),
                    'treatment_indicator'             => $request->get('treatment_indicator'),
                    'associated_services_type'        => $request->get('associated_services_type'),
                    'associated_services'             => $request->get('associated_services'),
                    'referral_sts_code'               => $request->get('referral_sts_code'),
                    'pec'                             => $request->get('pec'),
                    'contract_type'                   => $request->get('contract_type') ? $request->get(
                        'contract_type'
                    ) : null,
                    'health_insurance_periods_rights' => Period::hydrate([
                        'begin_date' => $request->get('begin_date') !== '' ?
                            new DateTimeImmutable($request->get('begin_date')) : null,
                        'end_date'   => $request->get('end_date') !== '' ?
                            new DateTimeImmutable($request->get('end_date')) : null,
                    ]),
                    'paper_mode'                      => true,
                ]),
            ]
        );

        $service = new InvoicingService();

        $invoice = CJfseInvoiceView::getFromEntity(
            $service->setThirdPartyPayment(
                $request->get('invoice_id'),
                $complementary
            )
        );

        return $this->handleActionAfterComplementaryHealthInsurance($invoice);
    }

    public function setHealthInsuranceFromVitalCardRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([
            'invoice_id'      => CView::post('invoice_id', 'str notNull'),
            'third_party_amc' => CView::post('third_party_amc', 'num default|1'),
        ]);
    }

    public function setHealthInsuranceFromVitalCard(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));

        $invoice = (new InvoicingService())->getInvoice($request->get('invoice_id'));

        $complementary = ComplementaryHealthInsurance::hydrate([
            'third_party_amo'             => true,
            'third_party_amc'  => (int)$request->get('third_party_amc'),
            'health_insurance' => $invoice->getBeneficiary()->getHealthInsurance()
        ]);

        $service = new InvoicingService();

        $invoice = CJfseInvoiceView::getFromEntity(
            $service->setThirdPartyPayment(
                $request->get('invoice_id'),
                $complementary
            )
        );

        return $this->handleActionAfterComplementaryHealthInsurance($invoice);
    }

    public function setAdditionalHealthInsuranceRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([], [
            'invoice_id'                    => CView::post('invoice_id', 'str notNull'),
            'third_party_amc'               => CView::post('third_party_amc', 'enum list|0|1|2'),
            'number_b2'                     => CView::post('additional_insurance_number_b2', 'str notNull'),
            'subscriber_number'             => CView::post('additional_insurance_subscriber_number', 'str'),
            'convention_type'               => CView::post('additional_insurance_convention_type', 'str'),
            'secondary_criteria'            => CView::post('additional_insurance_secondary_criteria', 'str'),
            'begin_date'                    => CView::post('additional_insurance_begin_date', 'date'),
            'end_date'                      => CView::post('additional_insurance_end_date', 'date'),
            'reference_date'                => (int)CView::post('additional_insurance_reference_date', 'str'),
            'id'                            => CView::post('additional_insurance_id', 'str'),
            'pec'                           => CView::post('additional_insurance_pec', 'str'),
            'treatment_indicator'           => CView::post('additional_insurance_treatment_indicator', 'str'),
            'referral_sts_code'             => CView::post('additional_insurance_referral_sts_code', 'str'),
            'routing_code'                  => CView::post('additional_insurance_routing_code', 'str'),
            'host_id'                       => CView::post('additional_insurance_host_id', 'str'),
            'domain_name'                   => CView::post('additional_insurance_domain_name', 'str'),
            'associated_services_contract'  => CView::post('additional_insurance_associated_services_contract', 'str'),
            'services_type'                 => CView::post('additional_insurance_services_type', 'str'),
            'contract_type'                 => CView::post('additional_insurance_contract_type', 'str'),
        ]);
    }

    public function setAdditionalHealthInsurance(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));

        $complementary = ComplementaryHealthInsurance::hydrate(
            [
                'third_party_amo'  => true,
                'third_party_amc'  => (int)$request->get('third_party_amc'),
                'attack_victim'    => false,
                'additional_health_insurance' => AdditionalHealthInsurance::hydrate([
                    'number_b2'                     => $request->get('number_b2'),
                    'subscriber_number'             => $request->get('subscriber_number'),
                    'convention_type'               => $request->get('convention_type'),
                    'secondary_criteria'            => $request->get('secondary_criteria'),
                    'begin_date'                    => $request->get('begin_date') !== '' ?
                        new DateTimeImmutable($request->get('begin_date')) : null,
                    'end_date'                      => $request->get('end_date') !== '' ?
                        new DateTimeImmutable($request->get('end_date')) : null,
                    'reference_date'                => $request->get('reference_date'),
                    'id'                            => $request->get('id'),
                    'pec'                           => $request->get('pec'),
                    'treatment_indicator'           => $request->get('treatment_indicator'),
                    'referral_sts_code'             => $request->get('referral_sts_code'),
                    'routing_code'                  => $request->get('routing_code'),
                    'host_id'                       => $request->get('host_id'),
                    'domain_name'                   => $request->get('domain_name'),
                    'associated_services_contract'  => $request->get('associated_services_contract'),
                    'services_type'                 => $request->get('services_type'),
                    'contract_type'                 => $request->get('contract_type'),
                    'paper_mode'                    => true,
                ]),
            ]
        );

        $service = new InvoicingService();

        $invoice = CJfseInvoiceView::getFromEntity(
            $service->setThirdPartyPayment(
                $request->get('invoice_id'),
                $complementary
            )
        );

        return $this->handleActionAfterComplementaryHealthInsurance($invoice);
    }

    public function setAdditionalHealthInsuranceFromVitalCardRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([
            'invoice_id'      => CView::post('invoice_id', 'str notNull'),
            'third_party_amo' => CView::post('third_party_amo', 'bool default|1'),
            'third_party_amc' => CView::post('third_party_amc', 'num default|1'),
        ]);
    }

    public function setAdditionalHealthInsuranceFromVitalCard(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));

        $invoice = (new InvoicingService())->getInvoice($request->get('invoice_id'));

        $complementary = ComplementaryHealthInsurance::hydrate([
            'third_party_amo'             => (bool)$request->get('third_party_amo'),
            'third_party_amc'             => (int)$request->get('third_party_amc'),
            'additional_health_insurance' => $invoice->getBeneficiary()->getAdditionalHealthInsurance()
        ]);

        $service = new InvoicingService();

        $invoice = CJfseInvoiceView::getFromEntity(
            $service->setThirdPartyPayment(
                $request->get('invoice_id'),
                $complementary
            )
        );

        return $this->handleActionAfterComplementaryHealthInsurance($invoice);
    }

    private function handleActionAfterComplementaryHealthInsurance(
        CJfseInvoiceView $invoice
    ): JsonResponse {
        switch ($invoice->complementary_health_insurance->assistant->action) {
            case 1:
                $content = new SmartyResponse('invoicing/convention_selection', [
                    'invoice_id' => $invoice->id,
                    'assistant'  => $invoice->complementary_health_insurance->assistant,
                ]);

                $response = new JsonResponse([
                    'success' => true,
                    'title'   => 'CComplementaryHealthInsurance-action-convention_selection',
                    'html'    => base64_encode($content->getContent())
                ]);
                break;
            case 2:
                $content = new SmartyResponse('invoicing/formulas_selection', [
                    'invoice_id' => $invoice->id,
                    'selected_formula' => $invoice->complementary_health_insurance->formula,
                    'assistant'  => $invoice->complementary_health_insurance->assistant,
                ]);

                $response = new JsonResponse([
                    'success' => true,
                    'title'   => 'CComplementaryHealthInsurance-action-select_formula',
                    'html'    => base64_encode($content->getContent())
                ]);
                break;
            case 3:
                $response = new JsonResponse([]);
                break;
            default:
                $response = new JsonResponse([
                    'success' => true,
                    'message' => 'CComplementaryHealthInsurance-msg-modified'
                ]);
        }

        return $response;
    }

    public function setAmoServiceRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([
            'invoice_id' => CView::post('invoice_id', 'str notNull'),
            'code'       => CView::post('amo_service_code', 'str notNull'),
            'begin_date' => CView::post('amo_service_begin_date', 'str'),
            'end_date'   => CView::post('amo_service_end_date', 'str'),
        ]);
    }

    public function setAmoService(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));

        $complementary = ComplementaryHealthInsurance::hydrate([
            'third_party_amo'  => false,
            'third_party_amc'  => false,
            'attack_victim'    => false,
            'amo_service'      => AmoServicePeriod::hydrate([
                'code' => $request->get('code'),
                'begin_date' => $request->get('begin_date') != ''
                    ? new DateTimeImmutable($request->get('begin_date')) : null,
                'end_date' => $request->get('end_date') != ''
                    ? new DateTimeImmutable($request->get('end_date')) : null,
            ]),
        ]);

        $service = new InvoicingService();
        $service->setThirdPartyPayment($request->get('invoice_id'), $complementary);

        return new JsonResponse(
            [
                'success' => true,
                'message' => 'CComplementaryHealthInsurance-msg-modified',
            ]
        );
    }

    public function selectConventionRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([
            'invoice_id'    => CView::post('invoice_id', 'str'),
            'convention_id' => CView::post('convention_id', 'str'),
        ]);
    }

    public function selectConvention(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));

        $service = new InvoicingService();
        $invoice = CJfseInvoiceView::getFromEntity(
            $service->selectConvention(
                $request->get('invoice_id'),
                $request->get('convention_id')
            )
        );

        return $this->handleActionAfterComplementaryHealthInsurance($invoice);
    }

    public function showConventionsListRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request(['invoice_id' => CView::post('invoice_id', 'str notNull')]);
    }

    public function showConventionsList(Request $request): SmartyResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));

        $invoice = CJfseInvoiceView::getFromEntity((new InvoicingService())->getInvoice($request->get('invoice_id')));

        return new SmartyResponse('invoicing/convention_selection', [
            'invoice_id' => $invoice->id,
            'assistant'  => $invoice->complementary_health_insurance->assistant,
        ]);
    }

    public function showFormulasListRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request(['invoice_id' => CView::post('invoice_id', 'str notNull')]);
    }

    public function showFormulasList(Request $request): SmartyResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));

        $invoice = CJfseInvoiceView::getFromEntity((new InvoicingService())->getInvoice($request->get('invoice_id')));

        return new SmartyResponse('invoicing/formulas_selection', [
            'invoice_id' => $invoice->id,
            'selected_formula' => $invoice->complementary_health_insurance->formula,
            'assistant'  => $invoice->complementary_health_insurance->assistant,
        ]);
    }

    public function selectFormulaRequest(): Request
    {
        CCanDo::checkEdit();

        $parameters = [];
        foreach (CView::post('parameters', ['str', 'default' => []]) as $parameter) {
            $parameters[] = json_decode(stripslashes($parameter), true);
        }

        return new Request(
            [
                'invoice_id'     => CView::post('invoice_id', 'str notNull'),
                'formula_number' => CView::post('formula_number', 'str notNull'),
                'parameters'     => $parameters,
            ]
        );
    }

    public function selectFormula(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));

        $service = new InvoicingService();
        $invoice = CJfseInvoiceView::getFromEntity(
            $service->selectFormula(
                $request->get('invoice_id'),
                $request->get('formula_number'),
                $request->get('parameters')
            )
        );

        return new JsonResponse(['success' => true]);
    }
}
