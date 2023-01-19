<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use DateTime;
use Ox\Core\CView;
use Ox\Mediboard\Jfse\Domain\Stats\StatsService;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Ox\Mediboard\Jfse\Utils;
use Ox\Mediboard\Jfse\ViewModels\Stats\CStatResult;
use Symfony\Component\HttpFoundation\Request;

final class StatsController extends AbstractController
{
    /** @var string[][] */
    public static $routes = [
        "index" => [
            "method"  => "index",
            "request" => "emptyRequest",
        ],
        "results" => [
            "method" => "results",
        ],
    ];

    public function index(): SmartyResponse
    {
        return new SmartyResponse('stats/index');
    }

    public function resultsRequest(): Request
    {
        $data = [
            "choices" => array_map('intval', CView::post("choices", "str notNull")),
            "begin"   => CView::post("begin", "date"),
            "end"     => CView::post("end", "date"),
            "jfse_id" => (int)CView::post("jfse_id", "num notNull"),
        ];

        return new Request([], $data);
    }

    public function results(Request $request): SmartyResponse
    {
        // IMPORTANT: The Jfse id must be set before the service call (= new client = new ApiAuthenticator
        // that depends on the jfse_id
        Utils::setJfseUserId($request->get('jfse_id'));

        $service = new StatsService();

        $begin = ($request->get('begin')) ? new DateTime($request->get('begin')) : null;
        $end   = ($request->get('end')) ? new DateTime($request->get('end')) : null;

        $stat_requests = $service->makeStatRequestsFromIntChoices($request->get('choices'), $begin, $end);
        $stats_results = $service->getStats(...$stat_requests);

        $stats_results_vm = CStatResult::getFromEntity($stats_results);

        return new SmartyResponse('stats/display_results', ["result" => $stats_results_vm]);
    }

    /**
     * @inheritDoc
     */
    public static function getRoutePrefix(): string
    {
        return 'stats';
    }
}
