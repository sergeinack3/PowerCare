<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Jfse\DataModels\CJfseUser;
use Ox\Mediboard\Jfse\Domain\Noemie\ProvisioningService;
use Ox\Mediboard\Jfse\Domain\UserManagement\UserManagementService;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Ox\Mediboard\Jfse\Utils;
use Symfony\Component\HttpFoundation\Request;

/**
 * A controller for the provisioning feature
 */
class ProvisioningController extends AbstractController
{
    protected ProvisioningService $service;

    /** @var string[][] */
    protected static $routes = [
        'index'   => [
            'method'  => 'index',
            'request' => 'emptyRequest',
        ],
        'provisionData' => [
            'method'  => 'provisionData',
        ],
    ];

    /**
     * ProvisioningController constructor.
     *
     * @param string               $route
     * @param ProvisioningService|null $service
     */
    public function __construct(string $route, ProvisioningService $service = null)
    {
        parent::__construct($route);

        $this->service = $service ?? new ProvisioningService();
    }
    /**
     * @return string
     */
    public static function getRoutePrefix(): string
    {
        return 'provisioning';
    }

    /**
     * @return SmartyResponse
     * @throws \Exception
     */
    public function index(): SmartyResponse
    {
        CCanDo::checkAdmin();

        return new SmartyResponse('provisioning/index', [
            'users' => UserManagementService::getJfseUsersListByPerm()
        ]);
    }

    /**
     * @param Request $request
     *
     * @return SmartyResponse
     * @throws \Ox\Core\CMbModelNotFoundException
     */
    public function provisionData(Request $request): SmartyResponse
    {
        $user = CJfseUser::findOrFail($request->get('jfse_user_id'));

        Utils::setJfseUserId($user->jfse_id);

        $results = $this->service->provisionDataForUser($user, $request->get('year'));

        return new SmartyResponse('provisioning/inc_results', ['results' => $results]);
    }

    /**
     * @return Request
     * @throws \Exception
     */
    public function provisionDataRequest(): Request
    {
        CCanDo::checkAdmin();

        $year = CView::post('year', ['num', 'default' => null]);
        if ($year !== null) {
            $year = (int)$year;
        }

        return new Request([
            'jfse_user_id' => CView::post('jfse_user_id', 'ref class|CJfseUser notNull'),
            'year'         => $year,
        ]);
    }
}
