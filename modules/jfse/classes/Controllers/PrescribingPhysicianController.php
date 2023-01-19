<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use DateTimeImmutable;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Jfse\ApiClients\PrescribingPhysicianClient;
use Ox\Mediboard\Jfse\Domain\PrescribingPhysician\Physician;
use Ox\Mediboard\Jfse\Domain\PrescribingPhysician\PhysicianOriginEnum;
use Ox\Mediboard\Jfse\Domain\PrescribingPhysician\PhysicianTypeEnum;
use Ox\Mediboard\Jfse\Domain\PrescribingPhysician\PrescribingPhysicianService;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Ox\Mediboard\Jfse\Utils;
use Ox\Mediboard\Jfse\ViewModels\PrescribingPhysician\CPrescribingPhysician;
use Ox\Mediboard\Jfse\ViewModels\PrescribingPhysician\CJfsePrescription;
use Ox\Mediboard\Mediusers\CMediusers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class PrescribingPhysicianController extends AbstractController
{
    /** @var array */
    public static $routes = [
        "store"              => [
            "method" => "store",
        ],
        "create"             => [
            "method"  => "createPhysician",
        ],
        "storePhysician"     => [
            "method" => "storePhysician",
        ],
        "searchAutocomplete" => [
            "method" => "searchAutocomplete",
        ],
        "searchList"         => [
            "method" => "searchList",
        ],
        "searchForm"         => [
            "method"  => "searchForm",
        ],
        "delete"             => [
            "method" => "delete",
        ],
    ];

    /** @var PrescribingPhysicianService */
    private $service;

    public function __construct(string $route)
    {
        parent::__construct($route);

        $this->service = new PrescribingPhysicianService(CMediusers::get()->_guid, new PrescribingPhysicianClient());
    }

    public static function getRoutePrefix(): string
    {
        return "prescribingPhysician";
    }

    public function createPhysicianRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request(['jfse_user_id' => (int)CView::post('jfse_user_id', 'num')]);
    }

    public function createPhysician(Request $request): SmartyResponse
    {
        return new SmartyResponse("prescribing_physician/new_physician", [
            "prescribing_physician" => new CPrescribingPhysician(),
            "specialities_list"     => $this->service->getPhysicianSpecialitiesList(),
            'jfse_user_id'          => $request->get('jfse_user_id'),
        ]);
    }

    public function storePhysicianRequest(): Request
    {
        CCanDo::checkEdit();

        $data = [
            'jfse_user_id'     => (int)CView::post('jfse_user_id', 'str'),
            "last_name"        => CView::post("last_name", "str maxLength|25"),
            "first_name"       => CView::post("first_name", "str maxLength|25"),
            "invoicing_number" => CView::post("invoicing_number", "str length|9"),
            "speciality"       => CView::post("speciality", "str maxLength|2"),
            "type"             => CView::post("type", PhysicianTypeEnum::getProp()),
            "national_id"      => CView::post("national_id", "num length|11"),
            "structure_id"     => CView::post("structure_id", "str maxLength|14"),
        ];

        return new Request([], $data);
    }

    public function storePhysician(Request $request): SmartyResponse
    {
        Utils::setJfseUserId($request->get('jfse_user_id'));
        $success = $this->service->storePhysician(Physician::hydrate($request->request->all()));

        if ($success) {
            return SmartyResponse::message(
                "PrescribingPhysicianController-Stored physician",
                SmartyResponse::MESSAGE_SUCCESS
            );
        }

        return SmartyResponse::message(
            "PrescribingPhysicianController-An error occurred",
            SmartyResponse::MESSAGE_ERROR
        );
    }

    public function storeRequest(): Request
    {
        CCanDo::checkEdit();

        $data = [
            "invoice_id"          => CView::post("invoice_id", "str notNull"),
            "prescription_date"   => new DateTimeImmutable(CView::post("date", "date notNull")),
            "prescription_origin" => CView::post("origin", PhysicianOriginEnum::getProp()),
            "id"                  => CView::post("id", "num"),
            "last_name"           => CView::post("last_name", "str maxLength|25"),
            "first_name"          => CView::post("first_name", "str maxLength|25"),
            "invoicing_number"    => CView::post("invoicing_number", "str length|9"),
            "speciality"          => CView::post("speciality_id", "str maxLength|2"),
            "type"                => CView::post("type_id", PhysicianTypeEnum::getProp()),
            "national_id"         => CView::post("national_id", "num length|11"),
            "structure_id"        => CView::post("structure_id", "str maxLength|14"),
        ];

        return new Request([], $data);
    }

    public function store(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));
        $content = $request->request->all();

        $physician = Physician::hydrate($content);

        if ($physician->getId() && $this->service->hasPrescribingPhysician($physician->getId())) {
            $this->service->storePhysician($physician);
        }

        $success = $this->service->setPrescribingPhysician(
            $content["invoice_id"],
            $content["prescription_date"],
            $content["prescription_origin"],
            $physician
        );

        if ($success) {
            $data = [
                'success' => true,
                'message' => 'PrescribingPhysicianController-Prescribing physician set',
            ];
        } else {
            $data = [
                'error'   => true,
                'message' => 'PrescribingPhysicianController-An issue occurred',
            ];
        }

        return new JsonResponse($data);
    }

    public function searchAutocompleteRequest(): Request
    {
        CCanDo::checkRead();

        $autocomplete = CView::post("physician_autocomplete", ["str", "default" => ""]);
        $data         = [
            'jfse_user_id' => (int)CView::get('jfse_user_id', 'num'),
            "first_name"   => $autocomplete,
            "last_name"    => $autocomplete,
        ];

        return new Request([], $data);
    }

    public function searchAutocomplete(Request $request): SmartyResponse
    {
        Utils::setJfseUserId($request->get('jfse_user_id'));
        $matches = $this->getPhysiciansMatches($request);

        return new SmartyResponse("prescribing_physician/physician_autocomplete", ["matches" => $matches]);
    }

    /**
     * Finds matches on physicians using the first name, last name and national id (RPPS)
     * This is a OR search
     *
     * @param Request $request
     *
     * @return array
     */
    private function getPhysiciansMatches(Request $request): array
    {
        $first_name  = $request->request->get("first_name");
        $last_name   = $request->request->get("last_name");
        $national_id = $request->request->get("national_id", "");

        $physicians = $this->service->getPrescribingPhysiciansWithFilters(
            $first_name,
            $last_name,
            $national_id
        );

        $matches = [];
        foreach ($physicians as $_physician) {
            $physician = CPrescribingPhysician::getFromEntity($_physician);

            $speciality                  = $this->service->getPhysicianSpecialityByCode($_physician->getSpeciality());
            $physician->speciality_label = $speciality->getLabel();

            $type                  = $this->service->getPhysicianTypeByCode($_physician->getType());
            $physician->type_label = $type->getLabel();

            // Set speciality to string with a leading "0" (e.g. "01" and not 1)
            $physician->speciality = str_pad($physician->speciality, 2, "0", STR_PAD_LEFT);

            $matches[] = $physician;
        }

        return $matches;
    }

    public function searchFormRequest(): Request
    {
        CCando::checkRead();

        return new Request(['jfse_user_id' => (int)CView::post('jfse_user_id', 'num')]);
    }

    public function searchForm(Request $request): SmartyResponse
    {
        Utils::setJfseUserId($request->get('jfse_user_id'));
        return new SmartyResponse("prescribing_physician/search_form", [
            "physician"  => new CPrescribingPhysician(),
            'jfse_user_id' => $request->get('jfse_user_id'),
        ]);
    }

    public function searchListRequest(): Request
    {
        CCando::checkRead();

        $data = [
            'jfse_user_id'  => (int)CView::post('jfse_user_id', 'num'),
            "first_name"  => CView::post("first_name", ["str", "default" => ""]),
            "last_name"   => CView::post("last_name", ["str", "default" => ""]),
            "national_id" => CView::post("national_id", ["num", "length" => 11, "default" => ""]),
        ];

        return new Request([], $data);
    }

    public function searchList(Request $request): SmartyResponse
    {
        Utils::setJfseUserId($request->get('jfse_user_id'));
        $matches = $this->getPhysiciansMatches($request);

        return new SmartyResponse("prescribing_physician/search_list", ["matches" => $matches]);
    }

    public function deleteRequest(): Request
    {
        CCando::checkEdit();

        $data = [
            "id" => CView::post("id", "num"),
        ];

        return new Request([], $data);
    }

    public function delete(Request $request): SmartyResponse
    {
        $success = $this->service->deletePhysician($request->get("id"));

        if ($success) {
            return SmartyResponse::message(
                "PrescribingPhysicianController-Prescribing physician deleted",
                SmartyResponse::MESSAGE_SUCCESS
            );
        }

        return SmartyResponse::message(
            "PrescribingPhysicianController-An error occurred",
            SmartyResponse::MESSAGE_ERROR
        );
    }
}
