<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use DateTimeImmutable;
use Exception;
use Ox\Core\CView;
use Ox\Mediboard\Jfse\Domain\Invoicing\InvoicingService;
use Ox\Mediboard\Jfse\Domain\ProofAmo\ProofAmoService;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Ox\Mediboard\Jfse\Utils;
use Ox\Mediboard\Jfse\ViewModels\CProofAmo;
use Ox\Mediboard\Jfse\ViewModels\CProofAmoType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProofAmoController
 *
 * @package Ox\Mediboard\Jfse\Controllers
 */
final class ProofAmoController extends AbstractController
{
    /** @var string[][] */
    protected static $routes = [
        'add'   => [
            'method'  => 'addProofAmo',
            'request' => 'addProofAmoRequest',
        ],
        'store' => [
            'method'  => 'storeProof',
            'request' => 'storeProofRequest',
        ],
    ];
    /** @var ProofAmoService */
    private $proof_amo_service;

    /**
     * ProofAmoController constructor.
     *
     * @param string               $route
     * @param ProofAmoService|null $service
     */
    public function __construct(string $route, ProofAmoService $service = null)
    {
        parent::__construct($route);

        $this->proof_amo_service = $service ?? new ProofAmoService();
    }


    /**
     * @inheritDoc
     */
    public static function getRoutePrefix(): string
    {
        return "proofAMO";
    }

    /**
     * @return Request
     * @throws Exception
     */
    public function addProofAmoRequest(): Request
    {
        $invoice_id = CView::post("invoice_id", "str");

        return new Request([], ["invoice_id" => $invoice_id]);
    }

    /**
     * Display form to add a new proof that will be sent to Jfse
     *
     * @param Request $request
     *
     * @return Response
     */
    public function addProofAmo(Request $request): Response
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));
        $entity_types = $this->proof_amo_service->listProofTypes();
        $types        = array_map(
            function ($type) {
                return CProofAmoType::getFromEntity($type);
            },
            $entity_types
        );

        $invoice = (new InvoicingService())->getInvoice($request->get('invoice_id'));
        $proof = new CProofAmo();
        if ($invoice->getProofAmo()) {
            $proof = CProofAmo::getFromEntity($invoice->getProofAmo());
        }

        $proof->invoice_id = $request->get('invoice_id');

        $tpl_vars = [
            "types"    => $types,
            "proofAMO" => $proof,
        ];

        return new SmartyResponse("proofAmo/edit_proof_amo", $tpl_vars);
    }

    /**
     * - Invoice id is the invoice which will be attached to the proof (Mandatory)
     * - Nature is the type of proof based on a given list (Mandatory)
     * - Date is the validity of the proof (Optional)
     * - Origin is origin code (or organization code if the nature is the "carte vitale") (Optional)
     *
     * @return Request
     * @throws Exception
     */
    public function storeProofRequest(): Request
    {
        $codes = array_map(
            function ($type) {
                return $type->getCode();
            },
            $this->proof_amo_service->listProofTypes()
        );
        // Codes must look like 0|1|2|3
        $codes_enum = implode('|', $codes);

        $invoice_id = (int)CView::post("invoice_id", "str notNull");
        $nature     = (int)CView::post("nature", "enum list|$codes_enum notNull");
        $date       = CView::post("date", "date");
        $origin     = (int)CView::post("origin", "num");

        $data = [
            "invoice_id" => $invoice_id,
            "nature"     => $nature,
            "date"       => (!$date) ? null : new DateTimeImmutable($date),
            "origin"     => $origin,
        ];

        return new Request([], $data);
    }

    /**
     * Save and send the proof to Jfse
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function storeProof(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));
        $proofAMO_service = new ProofAmoService();
        $is_saved         = $proofAMO_service->saveProofAmo(
            $request->get('invoice_id'),
            $request->get('nature'),
            $request->get('date'),
            $request->get('origin')
        );

        if ($is_saved) {
            return new JsonResponse(['success' => true, 'message' => "CProofAmo-Added"]);
        }

        return JsonResponse(['success' => false, 'error' => "CProofAmo-Not added"]);
    }
}
