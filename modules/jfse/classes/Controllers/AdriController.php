<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Jfse\Domain\Adri\AdriService;
use Ox\Mediboard\Jfse\Utils;
use Ox\Mediboard\Patients\CPatient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class AdriController extends AbstractController
{
    /** @var string[][] */
    public static $routes = [
        "patient/update" => [
            "method" => "updatePatient",
        ],
        'invoice' => [
            'method' => 'getAdriInvoice'
        ]
    ];

    public function updatePatientRequest(): Request
    {
        $data = [
            "patient_id" => CView::post("patient_id", "ref class|CPatient notNull"),
        ];

        return new Request([], $data);
    }

    public function updatePatient(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromGroup(CGroups::loadCurrent());
        $beneficiary = (new AdriService())->getFromCPatient(CPatient::findOrFail($request->get('patient_id')));

        return new JsonResponse($beneficiary);
    }

    public function getAdriInvoiceRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request(['invoice_id' => CView::post('invoice_id', 'str notNull')]);
    }

    public function getAdriInvoice(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));
        (new AdriService())->getInfosInvoiceAdri($request->get('invoice_id'));

        return new JsonResponse(['success' => true]);
    }

    public static function getRoutePrefix(): string
    {
        return 'adri';
    }
}
