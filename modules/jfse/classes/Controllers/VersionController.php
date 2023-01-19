<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use Ox\Core\CView;
use Ox\Mediboard\Jfse\Domain\Version\VersionService;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Symfony\Component\HttpFoundation\Request;

final class VersionController extends AbstractController
{
    /** @var string[][] */
    public static $routes = [
        "software" => [
            "method" => "software",
        ],
        "api"  => [
            "method" => "api",
        ],
    ];

    /** @var VersionService */
    private $service;

    /**
     * VersionController constructor.
     */
    public function __construct(string $route)
    {
        parent::__construct($route);

        $this->service = new VersionService();
    }

    public static function getRoutePrefix(): string
    {
        return "version";
    }

    public function softwareRequest(): Request
    {
        $data = [
            "jfse_id" => CView::post("jfse_id", "num notNull")
        ];

        return new Request([], $data);
    }

    public function software(Request $request): SmartyResponse
    {
        $version = $this->service->getVersion($request->get("jfse_id"));

        return new SmartyResponse("version/software", ["version" => $version]);
    }

    public function apiRequest(): Request
    {
        $data = [
            "code" => CView::post("code_cps", "num length|4"),
        ];

        return new Request([], $data);
    }

    public function api(Request $request): SmartyResponse
    {
        $api_version = $this->service->getApiVersion($request->get("code"));

        return new SmartyResponse("version/api", ["api_version" => $api_version]);
    }
}
