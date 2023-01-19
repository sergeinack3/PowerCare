<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use Exception;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Jfse\Domain\Cps\CpsService;
use Ox\Mediboard\Jfse\Domain\Substitute\SubstitutionService;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Ox\Mediboard\Jfse\Utils;
use Ox\Mediboard\Jfse\ViewModels\CCpsCard;
use Ox\Mediboard\Jfse\ViewModels\CSubstitute;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CpsController
 *
 * @package Ox\Mediboard\Jfse\Controllers
 */
final class CpsController extends AbstractController
{
    /** @var string[][] */
    protected static $routes = [
        'read'  => [
            'method'  => 'read',
            'request' => 'getReadRequest',
        ],
        'index' => [
            'method'  => 'index',
            'request' => 'getIndexRequest',
        ],
        'substituteSession/deactivate' => [
            'method' => 'deactivateSubstituteSession'
        ]
    ];

    /**
     * @return Request
     */
    public function getIndexRequest(): Request
    {
        return new Request();
    }

    /**
     * Displays the index view for the CPS
     *
     * @return Response
     */
    public function index(): Response
    {
        return new SmartyResponse('cps/index');
    }

    /**
     * @return Request
     * @throws Exception
     */
    public function getReadRequest(): Request
    {
        return new Request([
            'display_data' => (bool)CView::post('display_data', 'bool default|0')
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function read(Request $request): Response
    {
        Utils::setJfseUserIdFromMediuser();

        $service = new CpsService();
        $card    = $service->read($request->request->get('code'));
        $service->loadUsersFromCard($card);

        $substitute = false;
        if ($card->hasSubstitutionSession()) {
            $substitute = CSubstitute::getFromEntity(
                (new SubstitutionService())->getSubstitute($card->getFirstSubstituteNationalId())
            );
        }

        $cps = CCpsCard::getFromEntity($card);

        $template = 'read';
        if ($request->get('display_data')) {
            $template = 'view';
        }

        return new SmartyResponse("cps/$template", [
            'cps' => $cps,
            'substitute' => $substitute,
            'display_data' => $request->get('display_data')
        ]);
    }

    public function deactivateSubstituteSession(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromMediuser();

        (new SubstitutionService())->deactivateSession($request->get('substitute_id'));

        return new JsonResponse(['success' => true]);
    }

    public function deactivateSubstituteSessionRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([
            'substitute_id' => (int)CView::post('substitute_id', 'num notNull')
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function getRoutePrefix(): string
    {
        return 'cps';
    }
}
