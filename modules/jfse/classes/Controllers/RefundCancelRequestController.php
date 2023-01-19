<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Jfse\Domain\RefundCancelRequest\RefundCancelRequestService;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Ox\Mediboard\Jfse\ViewModels\RefundCancelRequest\CRefundCancelRequest;
use Ox\Mediboard\Jfse\ViewModels\RefundCancelRequest\CRefundCancelRequestDetails;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RefundRequestCancelController
 *
 * @package Ox\Mediboard\Jfse\Controllers
 */
final class RefundCancelRequestController extends AbstractController
{
    /** @var RefundCancelRequestService */
    protected $refund_cancel_request_service;

    /** @var string[][] */
    protected static $routes = [
        "searchForm" => [
            "method"  => "searchForm",
            "request" => "emptyRequest",
        ],
        "search"     => [
            "method" => "searchRefundCancelRequests",
        ],
        "edit"       => [
            "method"  => "edit",
            "request" => "emptyRequest",
        ],
        "store"      => [
            "method" => "store",
        ],
        "details"    => [
            "method" => "getRefundCancelRequestDetails",
        ],
    ];

    /**
     * @inheritDoc
     */
    public static function getRoutePrefix(): string
    {
        return "refundrequestcancel";
    }

    /**
     * RefundCancelRequestController constructor.
     *
     * @param string $route
     */
    public function __construct(string $route)
    {
        parent::__construct($route);

        $this->refund_cancel_request_service = new RefundCancelRequestService();
    }

    /**
     * @param Request $request
     *
     * @return SmartyResponse
     */
    public function searchForm(Request $request): SmartyResponse
    {
        return new SmartyResponse(
            "refund_cancel_request/search_form",
            [
                "jfse_id" => 1, // Gérer l'utilisateur
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return SmartyResponse
     */
    public function searchRefundCancelRequests(Request $request): SmartyResponse
    {
        $refund_cancel_requests = [];

        $data = $this->refund_cancel_request_service->getListe(
            $request->get("idJfse"),
            $request->get("dateDebut"),
            $request->get("dateFin"),
            $request->get("noFacture"),
            $request->get("idFacture")
        );

        foreach ($data as $refund_cancel_request) {
            $refund_cancel_requests[] = CRefundCancelRequest::getFromEntity($refund_cancel_request);
        }

        $vars = [
            "refundcancelrequests" => $refund_cancel_requests,
        ];

        return new SmartyResponse('refund_cancel_request/list', $vars);
    }

    /**
     * @return Request
     * @throws \Exception
     */
    public function searchRefundCancelRequestsRequest(): Request
    {
        $data = [
            "idJfse"    => CView::post('jfse_id', 'num notNull'),
            "dateDebut" => CView::post('date_debut', 'str'),
            "dateFin"   => CView::post('date_fin', 'str'),
            "noFacture" => CView::post('invoice_number', 'str'),
            "idFacture" => CView::post('invoice_id', 'str'),
        ];

        return new Request([], $data);
    }

    /**
     * @param Request $request
     *
     * @return SmartyResponse
     */
    public function edit(Request $request): SmartyResponse
    {
        $dre_annulation = new CRefundCancelRequest();

        $date = CMbDT::format(null, "%Y%m%d");

        return new SmartyResponse(
            'refund_cancel_request/edit',
            [
                "refundrequestcancel" => $dre_annulation,
                "date_elaboration"    => $date,
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return SmartyResponse
     */
    public function store(Request $request): SmartyResponse
    {
        $is_saved = $this->refund_cancel_request_service->save(
            $request->get("idFacture"),
            $request->get("dateElaboration"),
            $request->get("securisation")
        );

        if (!$is_saved) {
            return SmartyResponse::message(
                'CRefundCancelRequest-not updated',
                SmartyResponse::MESSAGE_WARNING
            );
        }

        return SmartyResponse::message(
            'CRefundCancelRequest-updated',
            SmartyResponse::MESSAGE_SUCCESS
        );
    }

    /**
     * @return Request
     * @throws \Exception
     */
    public function storeRequest(): Request
    {
        CCanDo::checkEdit();

        $data = [
            "idFacture"       => CView::post('invoice_id', 'str notNull'),
            "dateElaboration" => CView::post('date_elaboration', 'str notNull'),
            "securisation"    => CView::post('securisation', 'str notNull'),
        ];

        return new Request([], $data);
    }

    /**
     * @param Request $request
     *
     * @return SmartyResponse
     */
    public function getRefundCancelRequestDetails(Request $request): SmartyResponse
    {
        $entity = $this->refund_cancel_request_service->getDetails($request->get('idFacture'));

        $refund_cancel_request_details = CRefundCancelRequestDetails::getFromEntity($entity);

        return new SmartyResponse(
            'refund_cancel_request/details',
            [
                "refund_cancel_request_details" => $refund_cancel_request_details,
                "invoice_id"                    => $request->get('idFacture'),
            ]
        );
    }

    /**
     * @return Request
     * @throws \Exception
     */
    public function getRefundCancelRequestDetailsRequest(): Request
    {
        CCanDo::checkRead();

        return new Request(
            [],
            [
                "idFacture" => CView::post('invoice_id', 'str notNull'),
            ]
        );
    }
}
