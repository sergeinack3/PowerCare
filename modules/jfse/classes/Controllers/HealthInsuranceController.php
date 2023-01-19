<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Ox\Mediboard\Jfse\ViewModels\CHealthInsurance;
use Symfony\Component\HttpFoundation\Request;
use Ox\Mediboard\Jfse\Domain\HealthInsurance\HealthInsuranceService;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class HealthInsuranceController
 *
 * @package Ox\Mediboard\Jfse\Controllers
 */
final class HealthInsuranceController extends AbstractController
{
    /** @var string[][] */
    protected static $routes = [
        "search"             => [
            "method" => "search",
        ],
        "searchAutocomplete" => [
            "method" => "searchAutocomplete",
        ],
        "store"              => [
            "method" => "store",
        ],
        "edit"               => [
            "method" => "edit",
        ],
        "delete"             => [
            "method" => "delete",
        ],
    ];

    /**
     * @inheritDoc
     */
    public static function getRoutePrefix(): string
    {
        return "healthinsurance";
    }

    /** @var HealthInsuranceService */
    private $health_insurance_service;

    /**
     * HealthInsuranceController constructor.
     *
     * @param string $route
     */
    public function __construct(string $route)
    {
        parent::__construct($route);

        $this->health_insurance_service = new HealthInsuranceService();
    }

    /**
     * @return JsonResponse
     */
    public function search(Request $request): SmartyResponse
    {
        $type    = $request->get('typeOrganisme');
        $mode    = $request->get('mode');
        $nom     = $request->get('nom');
        $ids     = $request->get('lstIdJfse');
        $etab_id = $request->get('idEtablissement');

        $insurances = [];
        $responses  = $this->health_insurance_service->search($type, $mode, $nom, $ids, $etab_id);
        foreach ($responses as $row) {
            $insurances[] = CHealthInsurance::getFromEntity($row);
        }

        return new SmartyResponse('health_insurance/search', ['matches' => $insurances]);
    }

    public function searchRequest(): Request
    {
        CCanDo::checkRead();
        $data =
            [
                'typeOrganisme'   => CView::post('typeOrganisme', 'num notNull'),
                'mode'            => CView::post('mode', 'num notNull default|0'),
                'nom'             => CView::post('nom', 'str'),
                'idEtablissement' => CView::post('idEtablissement', 'num'),
                'lstIdJfse'       => CView::post('lstIdJfse', 'str'),
            ];

        return new Request([], $data);
    }

    /**
     * @param Request $request
     *
     * @return SmartyResponse
     */
    public function searchAutocomplete(Request $request): SmartyResponse
    {
        $type = $request->get('typeOrganisme');
        $mode = $request->get('mode');
        $nom  = $request->get('nom');

        $insurances = [];
        $responses  = $this->health_insurance_service->search($type, $mode, $nom);
        foreach ($responses as $row) {
            $insurances[] = CHealthInsurance::getFromEntity($row);
        }

        return new SmartyResponse('health_insurance/health_insurance_autocomplete', ['matches' => $insurances]);
    }

    /**
     * @return Request
     * @throws \Exception
     */
    public function searchAutocompleteRequest(): Request
    {
        CCanDo::checkRead();

        $autocomplete = CView::post('search_health_insurance', 'str');
        $data         =
            [
                'typeOrganisme' => 1,
                'mode'          => 1,
                'nom'           => $autocomplete,
            ];

        return new Request([], $data);
    }

    /**
     * @param Request $request
     *
     * @return SmartyResponse
     */
    public function store(Request $request): SmartyResponse
    {
        $service = new HealthInsuranceService();
        $code    = $request->get('code');
        $name    = $request->get('name');
        $service->save($code, $name);

        return SmartyResponse::message('CHealthInsurance-Saved', SmartyResponse::MESSAGE_SUCCESS);
    }

    /**
     * @return Request
     * @throws \Exception
     */
    public function storeRequest(): Request
    {
        CCanDo::checkEdit();
        $data =
            [
                'code' => CView::post('code', 'str notNull'),
                'nom'  => CView::post('nom', 'str notNull'),
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
        $code = $request->get('code');
        $vars =
            [
                "code"     => $code,
                "mutuelle" => new CHealthInsurance(),
            ];

        return new SmartyResponse('health_insurance/edit', $vars);
    }

    /**
     * @return Request
     * @throws \Exception
     */
    public function editRequest(): Request
    {
        CCanDo::checkEdit();
        $data = [
            'code' => CView::post('code', 'str notNull'),
        ];

        return new Request([], $data);
    }
}
