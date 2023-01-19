<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Jfse\Domain\History\DataGroupTypeEnum;
use Ox\Mediboard\Jfse\Domain\History\HistoryService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class HistoryController extends AbstractController
{
    /** @var string[][] */
    public static $routes = [
        "dataGroups/view" => [
            "method" => "viewDataGroups",
        ],
    ];

    public static function getRoutePrefix(): string
    {
        return 'history';
    }

    public static function viewDataGroups(Request $request): Response
    {
        (new HistoryService())->getDataGroups($request->get('invoice_id'), new DataGroupTypeEnum($request->get('type')));

        return new JsonResponse(['success'=> true]);
    }

    public static function viewDataGroupsRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([], [
            'invoice_id' => CView::post('invoice_id', 'str'),
            'type'       => (int)CView::post('type', DataGroupTypeEnum::getProp())
        ]);
    }
}
