<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Jfse\ApiClients\UserManagementClient;
use Ox\Mediboard\Jfse\DataModels\CJfsePatient;
use Ox\Mediboard\Jfse\DataModels\CJfseUser;
use Ox\Mediboard\Jfse\Domain\Gui\JfseGuiService;
use Ox\Mediboard\Jfse\Domain\Invoicing\InvoicingService;
use Ox\Mediboard\Jfse\Domain\Invoicing\SecuringModeEnum;
use Ox\Mediboard\Jfse\Domain\UserManagement\UserManagementService;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Ox\Mediboard\Jfse\Utils;
use Ox\Mediboard\Jfse\ViewModels\Invoicing\CJfseInvoiceView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Ssr\CModulateurCsARR;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class JfseGuiController extends AbstractController
{
    /** @var string[][] */
    public static $routes = [
        'index'                  => [
            'method' => 'index',
        ],
        'admin/settings'         => [
            'method' => 'adminSettings'
        ],
        'cps/read'               => [
            'method' => 'readCps',
        ],
        'users/manage'           => [
            'method' => 'manageUsers',
        ],
        'establishments/manage'  => [
            'method' => 'manageEstablishments',
        ],
        'formula/manage' => [
            'method' => 'manageFormula',
        ],
        'user/settings'          => [
            'method' => 'userSettings',
        ],
        'invoice/create'         => [
            'method' => 'createInvoice'
        ],
        'invoice/index'          => [
            'method' => 'invoiceIndex',
        ],
        'invoice/handleValidation' => [
            'method' => 'validateInvoice'
        ],
        'invoice/view'           => [
            'method' => 'viewInvoice',
        ],
        'invoice/dashboard'      => [
            'method' => 'invoiceDashboard',
        ],
        'actions'                => [
            'method' => 'actions',
        ],
        'scor/dashboard'         => [
            'method' => 'scorDashboard',
        ],
        'globalTeletransmission' => [
            'method' => 'globalTeletransmission',
        ],
        'noemie/manageReturns'   => [
            'method' => 'manageNoemieReturns',
        ],
        'tla/manage'             => [
            'method' => 'manageTLA',
        ],
        'version/mb'             => [
            'method'  => 'getMediboardModuleVersion',
        ],
        'version/module'         => [
            'method' => 'moduleVersion',
        ],
        'version/api'            => [
            'method' => 'apiVersion'
        ],
        'vitalCard/read' => [
            'method' => 'readVitalCard'
        ],
        'vitalCard/handleReading' => [
            'method' => 'handleVitalReading'
        ]
    ];

    /** @var JfseGuiService */
    private $gui_service;

    /**
     * @inheritDoc
     */
    public static function getRoutePrefix(): string
    {
        return 'gui';
    }

    /**
     * ConventionController constructor.
     *
     * @param string $route
     */
    public function __construct(string $route)
    {
        parent::__construct($route);

        $this->gui_service = new JfseGuiService();
    }

    public function index(): SmartyResponse
    {
        return new SmartyResponse('gui/index');
    }

    /**
     * @return Request
     */
    public function indexRequest(): Request
    {
        CCanDo::checkAdmin();

        return new Request();
    }

    public function adminSettings(Request $request): SmartyResponse
    {
        Utils::setJfseUserId(0);

        return new SmartyResponse('gui/admin_settings', $this->gui_service->settings());
    }

    public function adminSettingsRequest(): Request
    {
        CCanDo::checkAdmin();

        return new Request();
    }

    public function manageUsers(): JsonResponse
    {
        return new JsonResponse($this->gui_service->manageUsers());
    }

    public function manageUsersRequest(): Request
    {
        return new Request();
    }

    public function manageEstablishments(): JsonResponse
    {
        return new JsonResponse($this->gui_service->manageEstablishments());
    }

    public function manageEstablishmentsRequest(): Request
    {
        return new Request();
    }

    public function manageFormula(): SmartyResponse
    {
        Utils::setJfseUserId(0);

        return new SmartyResponse('gui/manage_sts_formula', $this->gui_service->manageFormula());
    }

    public function manageFormulaRequest(): Request
    {
        return new Request();
    }

    public function userSettings(Request $request): JsonResponse
    {
        if ($request->get('jfse_id')) {
            $jfse_id = (int)$request->get('jfse_id');
            Utils::setJfseUserId($request->get('jfse_id'));
        } else {
            $jfse_id = null;
            Utils::setJfseUserIdFromMediuser();
        }

        return new JsonResponse($this->gui_service->userSettings($jfse_id));
    }

    public function userSettingsRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request(['jfse_id' => $this->getJfseIdFromPost()]);
    }

    public function viewInvoice(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromMediuser();

        return new JsonResponse($this->gui_service->viewInvoice($request->get('invoice_id')));
    }

    public function viewInvoiceRequest(): Request
    {
        CCanDo::checkRead();

        return new Request([], ['invoice_id' => CView::post('invoice_id', 'str notNull')]);
    }

    public function createInvoice(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromMediuser();

        $consultation = CConsultation::findOrFail($request->get('consultation_id'));
        $service = new InvoicingService();
        $invoice = $service->initializeInvoice(
            $consultation,
            new SecuringModeEnum($request->get('securing_mode')),
            $request->get('situation_code')
        );

        return new JsonResponse($this->gui_service->viewInvoice($invoice->getId()));
    }

    public function createInvoiceRequest(): Request
    {
        CCanDo::checkRead();

        return new Request([], [
            'consultation_id' => CView::post('consultation_id', 'ref class|CConsultation notNull'),
            'securing_mode'   => (int)CView::post('securing_mode', SecuringModeEnum::getProp() . ' notNull'),
            'situation_code'  => (int)CView::post('situation_code', 'str')
        ]);
    }

    public function validateInvoice(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromMediuser();

        $invoice = CJfseInvoiceView::getFromEntity($this->gui_service->validateInvoice($request->get('invoice_id')));

        return new JsonResponse([
            'success' => true,
            'message' => utf8_encode(CAppUI::tr('CJfseInvoiceView-msg-validated', $invoice->invoice_number))
        ]);
    }

    public function validateInvoiceRequest(): Request
    {
        CCanDo::checkRead();

        return new Request([], ['invoice_id' => CView::post('invoice_id', 'str notNull')]);
    }

    public function invoiceDashboard(Request $request): JsonResponse
    {
        if ($request->get('jfse_id')) {
            Utils::setJfseUserId($request->get('jfse_id'));
        } else {
            Utils::setJfseUserIdFromMediuser();
        }

        return new JsonResponse($this->gui_service->invoiceDashboard());
    }

    public function invoiceDashboardRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request(['jfse_id' => $this->getJfseIdFromPost()]);
    }

    public function actions(Request $request): SmartyResponse
    {
        if ($request->get('jfse_id')) {
            Utils::setJfseUserId($request->get('jfse_id'));
        } else {
            Utils::setJfseUserIdFromMediuser();
        }

        return new SmartyResponse('gui/actions');
    }

    public function actionsRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request(['jfse_id' => $this->getJfseIdFromPost()]);
    }

    public function scorDashboard(Request $request): JsonResponse
    {
        if ($request->get('jfse_id')) {
            Utils::setJfseUserId($request->get('jfse_id'));
        } else {
            Utils::setJfseUserIdFromMediuser();
        }

        return new JsonResponse($this->gui_service->scorDashboard());
    }

    public function scorDashboardRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request(['jfse_id' => $this->getJfseIdFromPost()]);
    }

    public function globalTeletransmission(Request $request): JsonResponse
    {
        if ($request->get('jfse_id')) {
            Utils::setJfseUserId($request->get('jfse_id'));
        } else {
            Utils::setJfseUserIdFromMediuser();
        }

        return new JsonResponse($this->gui_service->globalTeletransmission());
    }

    public function globalTeletransmissionRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request(['jfse_id' => $this->getJfseIdFromPost()]);
    }

    public function manageNoemieReturns(Request $request): JsonResponse
    {
        if ($request->get('jfse_id')) {
            Utils::setJfseUserId($request->get('jfse_id'));
        } else {
            Utils::setJfseUserIdFromMediuser();
        }

        return new JsonResponse($this->gui_service->manageNoemieReturns());
    }

    public function manageNoemieReturnsRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request(['jfse_id' => $this->getJfseIdFromPost()]);
    }

    public function manageTLA(Request $request): JsonResponse
    {
        if ($request->get('jfse_id')) {
            Utils::setJfseUserId($request->get('jfse_id'));
        } else {
            Utils::setJfseUserIdFromMediuser();
        }

        return new JsonResponse($this->gui_service->manageTLA());
    }

    public function manageTLARequest(): Request
    {
        CCanDo::checkEdit();

        return new Request(['jfse_id' => $this->getJfseIdFromPost()]);
    }

    public function moduleVersion(Request $request): JsonResponse
    {
        if ($request->get('jfse_id')) {
            Utils::setJfseUserId($request->get('jfse_id'));
        } else {
            Utils::setJfseUserIdFromMediuser();
        }

        return new JsonResponse($this->gui_service->moduleVersion());
    }

    public function moduleVersionRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request(['jfse_id' => $this->getJfseIdFromPost()]);
    }

    public function apiVersion(Request $request): JsonResponse
    {
        if ($request->get('jfse_id')) {
            Utils::setJfseUserId($request->get('jfse_id'));
        } else {
            Utils::setJfseUserIdFromMediuser();
        }

        return new JsonResponse($this->gui_service->apiVersion());
    }

    public function apiVersionRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request(['jfse_id' => $this->getJfseIdFromPost()]);
    }

    public function invoiceIndex(Request $request): SmartyResponse
    {
        $consultation = CConsultation::findOrFail($request->get('consultation_id'));

        $patient = $consultation->loadRefPatient();
        $patient_data_model = CJfsePatient::getFromPatient($patient);
        $invoices = InvoicingService::getAllInvoicesFromConsultation($consultation);

        return new SmartyResponse('gui/invoice_index', [
            'consultation' => $consultation, 'patient_data_model' => $patient_data_model, 'invoices' => $invoices
        ]);
    }

    public function invoiceIndexRequest(): Request
    {
        CCanDo::checkRead();

        return new Request([], ['consultation_id' => CView::post('consultation_id', 'ref class|CConsultation notNull')]);
    }

    public function readCps(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromMediuser();
        return new JsonResponse($this->gui_service->readCps());
    }

    public function readCpsRequest(): Request
    {
        CCanDo::checkRead();

        return new Request([], []);
    }

    public function readVitalCard(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromMediuser();
        $consultation = CConsultation::findOrFail($request->get('consultation_id'));

        return new JsonResponse(array_map_recursive('utf8_encode', $this->gui_service->readVitalCard($consultation)));
    }

    public function readVitalCardRequest(): Request
    {
        CCanDo::checkRead();

        return new Request([], ['consultation_id' => CView::post('consultation_id', 'ref class|CConsultation notNull')]);
    }

    public function handleVitalReading(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromMediuser();
        $consultation = CConsultation::findOrFail($request->get('consultation_id'));

        return new JsonResponse(array_map_recursive(
            'utf8_encode',
            $this->gui_service->handleVitalReading($consultation, $request->get('data'))
        ));
    }

    public function handleVitalReadingRequest(): Request
    {
        CCanDo::checkRead();

        return new Request([], [
            'consultation_id' => CView::post('consultation_id', 'ref class|CConsultation notNull'),
            'data' => json_decode(utf8_encode(base64_decode(CView::post('data', 'str notNull'))), true)
        ]);
    }

    public function getMediboardModuleVersion(Request $request): SmartyResponse
    {
        if ($request->get('jfse_id')) {
            Utils::setJfseUserId($request->get('jfse_id'));
        } else {
            Utils::setJfseUserIdFromMediuser();
        }

        $software_name = 'OX Mediboard';
        if (CModule::getActive('oxCabinet')) {
            $software_name = 'TAMM';
        }

        $version = '2.2';

        return new SmartyResponse('version', ['software_name' => $software_name, 'version' => $version]);
    }

    public function getMediboardModuleVersionRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request(['jfse_id' => $this->getJfseIdFromPost()]);
    }

    private function getJfseIdFromPost(): ?int
    {
        $jfse_id = CView::post('jfse_user_id', 'num', true);
        $jfse_id = $jfse_id !== '' ? (int)$jfse_id : null;

        if ($jfse_id) {
            $jfse_user = new CJfseUser();
            $jfse_user->jfse_id = $jfse_id;
            $jfse_user->loadMatchingObject();

            if (!$jfse_user->_id) {
                $jfse_id = null;
            }
        }

        return $jfse_id;
    }
}
