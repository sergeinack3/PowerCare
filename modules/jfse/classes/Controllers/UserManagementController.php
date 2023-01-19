<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use Exception;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Jfse\DataModels\CJfseUser;
use Ox\Mediboard\Jfse\Domain\Cps\Card;
use Ox\Mediboard\Jfse\Domain\Cps\CpsService;
use Ox\Mediboard\Jfse\Domain\UserManagement\EmployeeCard;
use Ox\Mediboard\Jfse\Domain\UserManagement\Establishment;
use Ox\Mediboard\Jfse\Domain\UserManagement\EstablishmentService;
use Ox\Mediboard\Jfse\Domain\UserManagement\UserManagementService;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Ox\Mediboard\Jfse\Utils;
use Ox\Mediboard\Jfse\ViewModels\CCpsCard;
use Ox\Mediboard\Jfse\ViewModels\UserManagement\CEmployeeCard;
use Ox\Mediboard\Jfse\ViewModels\UserManagement\CJfseEstablishmentView;
use Ox\Mediboard\Jfse\ViewModels\UserManagement\CJfseUserView;
use Ox\Mediboard\Mediusers\CMediusers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UserManagementController extends AbstractController
{
    /** @var string[][] */
    protected static $routes = [
        'index'                       => [
            'method' => 'index',
        ],
        'employee_card/delete'        => [
            'method' => 'deleteEmployeeCard',
        ],
        'employee_card/edit'          => [
            'method' => 'editEmployeeCard',
        ],
        'employee_card/store'         => [
            'method' => 'storeEmployeeCard',
        ],
        'employee_cards/list'         => [
            'method' => 'listEmployeeCards',
        ],
        'establishments/autocomplete' => [
            'method' => 'autocompleteEstablishments',
        ],
        'establishments/list'         => [
            'method' => 'listEstablishments',
        ],
        'establishment/edit'          => [
            'method' => 'editEstablishment',
        ],
        'establishment/store'         => [
            'method' => 'storeEstablishment',
        ],
        'establishment/delete'        => [
            'method' => 'deleteEstablishment',
        ],
        'establishment/link'          => [
            'method' => 'linkEstablishment',
        ],
        'establishment/unlink'        => [
            'method' => 'unlinkEstablishment',
        ],
        'establishment/user/link'     => [
            'method' => 'linkUserToEstablishment',
        ],
        'establishment/user/unlink'   => [
            'method' => 'unlinkUserToEstablishment',
        ],
        'establishment/users/list'    => [
            'method' => 'listEstablishmentUsers',
        ],
        'users/list'                  => [
            'method' => 'listUsers',
        ],
        'user/delete'                 => [
            'method' => 'deleteUser',
        ],
        'user/link'                   => [
            'method' => 'linkUser',
        ],
        'user/unlink'                 => [
            'method' => 'unlinkUser',
        ],
        'user/linkEstablishment'      => [
            'method' => 'linkUserToEstablishment',
        ],
        'user/unlinkEstablishment'    => [
            'method' => 'unlinkUserToEstablishment',
        ],
        'user/view'                   => [
            'method' => 'viewUser',
        ],
        'user/info'                   => [
            'method' => 'userInfo',
        ],
        'user/create'                 => [
            'method' => 'createUser',
        ],
        'user/parameter/edit'         => [
            'method' => 'editUserParameter',
        ],
        'user/parameter/delete'       => [
            'method' => 'deleteUserParameter',
        ],
        'user/signature/edit'         => [
            'method' => 'editUserSignature',
        ],
        'user/signature/delete'       => [
            'method' => 'deleteUserSignature',
        ],
        'user/signature/isSet'        => [
            'method' => 'isUserSignatureSet',
        ],
        'autocomplete'                => [
            'method' => 'mediuserAutocomplete',
        ],
    ];

    /** @var Cache */
    protected $cps_cache;

    /**
     * @return Request
     */
    public function indexRequest(): Request
    {
        CCanDo::checkAdmin();

        return new Request();
    }

    /**
     * Displays the index view for the CPS
     *
     * @return Response
     */
    public function index(): Response
    {
        return new SmartyResponse('user_management/index');
    }

    public function deleteEmployeeCard(Request $request): Response
    {
        $service = new EstablishmentService();
        $result  = $service->deleteEmployeeCard(EmployeeCard::hydrate(['id' => $request->get('id')]));

        return new JsonResponse(['success' => $result]);
    }

    public function deleteEmployeeCardRequest(): Request
    {
        CCanDo::checkAdmin();

        return new Request(['id' => CView::post('id', 'str notNull')]);
    }

    public function editEmployeeCard(Request $request): Response
    {
        $employee_card                   = new CEmployeeCard();
        $employee_card->establishment_id = $request->request->get('establishment_id');

        return new SmartyResponse('user_management/employee_card_edit', ['employee_card' => $employee_card]);
    }

    public function editEmployeeCardRequest(): Request
    {
        CCanDo::checkAdmin();

        return new Request([], [
            'establishment_id' => CView::post('establishment_id', 'str notNull'),
        ]);
    }

    public function storeEmployeeCard(Request $request): Response
    {
        $service = new EstablishmentService();
        $result = $service->storeEmployeeCard(EmployeeCard::hydrate([
            'establishment_id' => $request->request->get('establishment_id'),
            'name'             => $request->request->get('name'),
            'invoicing_number' => $request->request->get('invoicing_number')
        ]));

        return new JsonResponse(['success' => $result]);
    }

    public function storeEmployeeCardRequest(): Request
    {
        CCanDo::checkAdmin();

        return new Request([], [
            'establishment_id' => CView::post('establishment_id', 'str notNull'),
            'name'             => CView::post('name', 'str notNull'),
            'invoicing_number' => CView::post('invoicing_number', 'str notNull'),
        ]);
    }

    public function listEmployeeCards(Request $request): Response
    {
        $service        = new EstablishmentService();
        $employee_cards = $service->listEmployeeCards(
            Establishment::hydrate(['id' => $request->request->get('establishment_id')])
        );

        return new SmartyResponse('user_management/employee_cards_list', [
            'employee_cards' => CEmployeeCard::getFromEmployeeCards($employee_cards)
        ]);
    }

    public function listEmployeeCardsRequest(): Request
    {
        CCanDo::checkAdmin();

        return new Request([], [
            'establishment_id' => CView::post('establishment_id', 'str notNull'),
        ]);
    }

    public function autocompleteEstablishments(Request $request): Response
    {
        $service        = new EstablishmentService();
        $establishments = $service->searchEstablishment($request->request->get('name'));

        return new SmartyResponse('user_management/CJfseEstablishment_autocomplete', [
            'establishments' => CJfseEstablishmentView::getFromEstablishments($establishments)
        ]);
    }

    public function autocompleteEstablishmentsRequest(): Request
    {
        CCanDo::checkAdmin();

        return new Request([], ['name' => CView::post('establishment_view', 'str')]);
    }

    public function listEstablishments(Request $request): Response
    {
        $start   = $request->request->get('start');
        $refresh = $request->request->get('refresh');

        $service             = new EstablishmentService();
        $establishments      = $service->listEstablishments();
        $establishment_views = CJfseEstablishmentView::getFromEstablishments($establishments, true, $start);

        $template = 'user_management/establishments_list';
        if (!$refresh) {
            $template .= '_index';
        }

        return new SmartyResponse($template, [
            'establishments' => $establishment_views, 'total' => count($establishments)
        ]);
    }

    public function listEstablishmentsRequest(): Request
    {
        CCanDo::checkAdmin();

        return new Request([], [
            'refresh'             => boolval(CView::post('refresh', 'bool default|0')),
            'start'               => intval(CView::post('start', 'num default|0'))
        ]);
    }

    public function editEstablishment(Request $request): Response
    {
        $id = $request->request->get('id');

        $entity = Establishment::hydrate([]);
        if ($id) {
            $service = new EstablishmentService();
            $entity  = $service->getEstablishment($id);
            $service->getEstablishmentConfiguration($entity);
            $service->listEmployeeCards($entity);
            $service->listUsersForEstablishment($entity);
        }

        $view = CJfseEstablishmentView::getFromEntity($entity);

        return new SmartyResponse('user_management/establishment_view', ['establishment' => $view]);
    }

    public function editEstablishmentRequest(): Request
    {
        CCanDo::checkAdmin();

        return new Request([], ['id' => CView::post('id', ['num', 'default' => null])]);
    }

    public function storeEstablishment(Request $request): Response
    {
        $service = new EstablishmentService();
        if (!$request->request->get('id')) {
            $result = $service->createEstablishment(Establishment::hydrate([
                'type'                 => intval($request->request->get('type')),
                'exoneration_label'    => $request->request->get('exoneration_label'),
                'health_center_number' => $request->request->get('health_center_number'),
                'name'                 => $request->request->get('name'),
                'category'             => $request->request->get('category'),
                'status'               => $request->request->get('status'),
                'invoicing_mode'       => $request->request->get('invoicing_mode')
            ]), $request->request->get('object_class'), $request->request->get('object_id'));
        } else {
            $result = $service->updateEstablishment(Establishment::hydrate([
                'id'                   => $request->request->get('id'),
                'type'                 => intval($request->request->get('type')),
                'exoneration_label'    => $request->request->get('exoneration_label'),
                'health_center_number' => $request->request->get('health_center_number'),
                'name'                 => $request->request->get('name'),
                'category'             => $request->request->get('category'),
                'status'               => $request->request->get('status'),
                'invoicing_mode'       => $request->request->get('invoicing_mode')
            ]));
        }

        return new JsonResponse(['success' => $result]);
    }

    public function storeEstablishmentRequest(): Request
    {
        CCanDo::checkAdmin();

        return new Request([], [
            'id'                   => CView::post('id', ['num', 'default' => null]),
            'type'                 => CView::post('type', ['num', 'default' => 0]),
            'exoneration_label'    => CView::post('exoneration_label', 'str'),
            'health_center_number' => CView::post('health_center_number', 'str'),
            'name'                 => CView::post('name', 'str'),
            'category'             => CView::post('category', 'str'),
            'status'               => CView::post('status', ['num', 'default' => 0]),
            'invoicing_mode'       => CView::post('invoicing_mode', 'str'),
            'object_class'         => CView::post('_object_class', [
                'enum',
                'list' => 'CFunctions|CGroups',
                'default' => null
            ]),
            'object_id'            => CView::post('_object_id', [
                'ref',
                'meta' => '_object_class',
                'default' => null
            ]),
        ]);
    }

    public function deleteEstablishment(Request $request): Response
    {
        $service = new EstablishmentService();
        $result  = $service->deleteEstablishment($request->request->get('id'));

        return new JsonResponse(['success' => $result]);
    }

    public function deleteEstablishmentRequest(): Request
    {
        CCanDo::checkAdmin();
        $id = CView::post('id', 'num notNull');

        return new Request([], ['id' => $id]);
    }

    public function linkEstablishment(Request $request): Response
    {
        $service = new EstablishmentService();
        $result  = $service->linkEstablishmentToObject(
            $request->request->get('establishment_id'),
            $request->request->get('object_class'),
            $request->request->get('object_id')
        );

        return new JsonResponse(['success' => $result]);
    }

    public function linkEstablishmentRequest(): Request
    {
        CCanDo::checkAdmin();

        return new Request([], [
            'establishment_id' => CView::post('establishment_id', 'num notNull'),
            'object_class'     => CView::post('object_class', 'enum list|CFunctions|CGroups notNull'),
            'object_id'        => CView::post('object_id', 'ref meta|object_class notNull'),
        ]);
    }

    public function unlinkEstablishment(Request $request): Response
    {
        $service = new EstablishmentService();
        $result  = $service->unlinkEstablishmentToObject($request->request->get('establishment_id'));

        return new JsonResponse(['success' => $result]);
    }

    public function unlinkEstablishmentRequest(): Request
    {
        CCanDo::checkAdmin();

        return new Request([], ['establishment_id' => CView::post('establishment_id', 'num notNull')]);
    }


    public function deleteUser(Request $request): Response
    {
        $service = new UserManagementService();
        $result  = $service->deleteUser($request->request->get('user_id'));

        return new JsonResponse(['success' => $result]);
    }

    public function deleteUserRequest(): Request
    {
        CCanDo::checkAdmin();
        $user_id = CView::post('user_id', 'num notNull');

        return new Request([], ['user_id' => $user_id]);
    }

    public function listUsers(Request $request): Response
    {
        $start   = $request->request->get('start');
        $refresh = $request->request->get('refresh');

        $service     = new UserManagementService();
        $users       = $service->listUsers(
            $request->request->get('last_name'),
            $request->request->get('first_name'),
            $request->request->get('invoicing_number'),
            $request->request->get('national_identifier')
        );
        $users_views = CJfseUserView::getFromUsers($users, true, $start);

        $template = 'user_management/users_list';
        if (!$refresh) {
            $template .= '_index';
        }

        return new SmartyResponse($template, ['users' => $users_views, 'total' => count($users), 'current' => $start]);
    }

    public function listUsersRequest(): Request
    {
        CCanDo::checkAdmin();

        $data = [
            'refresh'             => boolval(CView::post('refresh', 'bool default|0')),
            'start'               => intval(CView::post('start', 'num default|0')),
            'last_name'           => CView::post('last_name', ['str', 'default' => null]),
            'first_name'          => CView::post('first_name', ['str', 'default' => null]),
            'invoicing_number'    => CView::post('invoicing_number', ['str', 'default' => null]),
            'national_identifier' => CView::post('national_identifier', ['str', 'default' => null]),
        ];

        return new Request([], $data);
    }

    public function linkUser(Request $request): Response
    {
        $service = new UserManagementService();
        $result  = $service->linkUserToMediuser(
            $request->request->get('user_id'),
            $request->request->get('mediuser_id')
        );

        return new JsonResponse(['success' => $result]);
    }

    public function linkUserRequest(): Request
    {
        CCanDo::checkAdmin();

        $user_id     = CView::post('user_id', 'num notNull');
        $mediuser_id = CView::post('mediuser_id', 'ref class|CMediusers notNull');

        return new Request([], ['user_id' => $user_id, 'mediuser_id' => $mediuser_id]);
    }

    public function unlinkUser(Request $request): Response
    {
        $service = new UserManagementService();
        $result  = $service->unlinkUserToMediuser($request->request->get('user_id'));

        return new JsonResponse(['success' => $result]);
    }

    public function unlinkUserRequest(): Request
    {
        CCanDo::checkAdmin();

        $user_id = CView::post('user_id', 'num notNull');

        return new Request([], ['user_id' => $user_id]);
    }

    public function linkUserToEstablishment(Request $request): Response
    {
        $service = new EstablishmentService();
        $result  = $service->linkUserToEstablishment(
            $request->request->get('establishment_id'),
            $request->request->get('user_id')
        );

        return new JsonResponse(['success' => $result]);
    }

    public function linkUserToEstablishmentRequest(): Request
    {
        CCanDo::checkAdmin();

        return new Request([], [
            'user_id'          => CView::post('user_id', 'num notNull'),
            'establishment_id' => CView::post('establishment_id', 'num notNull'),
        ]);
    }

    public function unlinkUserToEstablishment(Request $request): Response
    {
        $service = new EstablishmentService();
        $result  = $service->unlinkUserFromEstablishment($request->request->get('user_id'));

        return new JsonResponse(['success' => $result]);
    }

    public function unlinkUserToEstablishmentRequest(): Request
    {
        CCanDo::checkAdmin();

        return new Request([], ['user_id' => CView::post('user_id', 'num notNull')]);
    }

    public function listEstablishmentUsers(Request $request): Response
    {
        $service       = new EstablishmentService();
        $establishment = Establishment::hydrate(['id' => $request->request->get('establishment_id')]);
        $service->listUsersForEstablishment($establishment);

        return new SmartyResponse('user_management/establishment_users_list', [
            'users' => CJfseUserView::getFromUsers($establishment->getUsers()),
            'establishment_id' => $establishment->getId()
        ]);
    }

    public function listEstablishmentUsersRequest(): Request
    {
        CCanDo::checkAdmin();

        return new Request([], ['establishment_id' => CView::post('establishment_id', 'num notNull')]);
    }

    public function viewUser(Request $request): Response
    {
        $service = new UserManagementService();
        $user    = $service->getUser($request->request->get('user_id'));
        if ($user->getEstablishmentId()) {
            $user->setEstablishment((new EstablishmentService())->getEstablishment($user->getEstablishmentId()));
        }

        $user_view = CJfseUserView::getFromEntity($user);

        return new SmartyResponse('user_management/user_view', ['user' => $user_view]);
    }

    public function viewUserRequest(): Request
    {
        CCanDo::checkAdmin();
        $user_id = CView::post('user_id', 'num notNull');

        return new Request([], ['user_id' => $user_id]);
    }

    public function createUser(Request $request): Response
    {
        if ($request->request->get('select_situation') && $request->request->get('situation_id')) {
            $this->setCpsCache();
            if ($card = $this->cps_cache->get()) {
                $this->cps_cache->rem();
                $card->selectSituation($request->request->get('situation_id'));
                $response = $this->createUserFromCps($card);
            } else {
                $response = SmartyResponse::message('CJfseUserView-error-creation', 'error');
            }
        } else {
            $cps_service = new CpsService();
            $card        = $cps_service->read();

            if ($card->countSituations() === 1) {
                $response = $this->createUserFromCps($card);
            } else {
                $this->setCpsCache();
                $this->cps_cache->put($card);

                $vars = [
                    'cps'            => CCpsCard::getFromEntity($card),
                    'callback_route' => 'user_management/user/create',
                ];

                $response = new SmartyResponse('cps/select_situation', $vars);
            }
        }

        return $response;
    }

    public function setCpsCache(Cache $cache = null): void
    {
        if (!$cache) {
            $cache = new Cache('Jfse-UserManagement', 'CpsCreationCache' . CMediusers::get()->_guid, Cache::OUTER, 120);
        }

        $this->cps_cache = $cache;
    }

    protected function createUserFromCps(Card $cps): Response
    {
        $user_service = new UserManagementService();
        $user         = $user_service->createUserFromCps($cps);

        $message = 'CJfseUserView-msg-created';
        $type    = 'info';
        if ($user->getId() === null) {
            $message = 'CJfseUserView-error-creation';
            $type    = 'error';
        }

        return SmartyResponse::message($message, $type);
    }

    public function createUserRequest(): Request
    {
        CCanDo::checkAdmin();
        $data = [
            'select_situation' => boolval(CView::post('select_situation', 'bool default|0')),
        ];

        if ($data['select_situation']) {
            $data['situation_id'] = CView::post('situation_id', 'num');
        }

        return new Request([], $data);
    }

    public function editUserParameter(Request $request): Response
    {
        Utils::setJfseUserId($request->request->get('user_id'));

        $service = new UserManagementService();
        $result  = $service->updateUserParameter(
            $request->request->get('parameter_id'),
            $request->request->get('value')
        );

        return new JsonResponse(['success' => $result]);
    }

    public function editUserParameterRequest(): Request
    {
        CCanDo::checkAdmin();
        $user_id      = CView::post('user_id', 'num notNull');
        $parameter_id = CView::post('parameter_id', 'num notNull');
        $value        = CView::post('value', 'str');

        return new Request([], ['user_id' => $user_id, 'parameter_id' => $parameter_id, 'value' => $value]);
    }

    public function deleteUserParameter(Request $request): Response
    {
        Utils::setJfseUserId($request->request->get('user_id'));

        $service = new UserManagementService();
        $result  = $service->deleteUserParameter($request->request->get('parameter_id'));

        return new JsonResponse(['success' => $result]);
    }

    public function deleteUserParameterRequest(): Request
    {
        CCanDo::checkAdmin();
        $user_id      = CView::post('user_id', 'num notNull');
        $parameter_id = CView::post('parameter_id', 'num notNull');

        return new Request([], ['user_id' => $user_id, 'parameter_id' => $parameter_id]);
    }

    public function editUserSignature(Request $request): Response
    {
        return new SmartyResponse('user_management/', []);
    }

    public function editUserSignatureRequest(): Request
    {
        CCanDo::checkAdmin();
        $user_id   = CView::post('user_id', 'num notNull');
        $signature = CView::post('signature', 'ref class|CFile');

        return new Request([], ['user_id' => $user_id]);
    }

    public function deleteUserSignature(Request $request): Response
    {
        return new SmartyResponse('user_management/', []);
    }

    public function deleteUserSignatureRequest(): Request
    {
        CCanDo::checkAdmin();
        $user_id = CView::post('user_id', 'num notNull');

        return new Request([], ['user_id' => $user_id]);
    }

    public function isUserSignatureSet(Request $request): Response
    {
        return new SmartyResponse('user_management/', []);
    }

    public function isUserSignatureSetRequest(): Request
    {
        CCanDo::checkAdmin();
        $user_id = CView::post('user_id', 'num notNull');

        return new Request([], ['user_id' => $user_id]);
    }

    public function mediuserAutocompleteRequest(): Request
    {
        CCanDo::checkRead();
        $data = [
            'name' => CView::post('name', 'str'),
        ];

        return new Request([], $data);
    }

    /**
     * @param Request $request
     *
     * @return SmartyResponse
     * @throws Exception
     */
    public function mediuserAutocomplete(Request $request): SmartyResponse
    {
        $name = $request->get('name');

        $service    = new UserManagementService();
        $jfse_users = $service->searchJfseUsersFromName($name);

        return new SmartyResponse('user_management/users_autocomplete', ['matches' => $jfse_users]);
    }

    public function userInfoRequest(): Request
    {
        $data = [
            "user_id" => CView::post("user_id", "ref class|CMediusers notNull")
        ];

        return new Request([], $data);
    }

    /**
     * @route user_management/user/info
     */
    public function userInfo(Request $request): JsonResponse
    {
        $jfse_user = CJfseUser::getFromMediuser(CMediusers::findOrFail($request->get('user_id')));
        $user      = (new UserManagementService())->getUser($jfse_user->jfse_id);
        $situation  = $user->getSituation();

        $data = [
            "rpps"       => $user->getNationalIdentificationNumber(),
            "speciality" => utf8_encode($situation->getSpecialityLabel()),
            "contracted_label" => $situation->getConventionLabel(),
            "invoicing_number" => $user->getInvoicingNumber()
        ];

        return new JsonResponse($data);
    }

    /**
     * @inheritDoc
     */
    public static function getRoutePrefix(): string
    {
        return 'user_management';
    }
}
