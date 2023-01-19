<?php
/**
 * @package Mediboard\EAI
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Controllers;

use Exception;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CController;
use Ox\Core\CMbArray;
use Ox\Interop\Eai\CEAIDispatcher;
use Ox\Interop\Eai\CEAIException;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CSenderHTTP;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CEAIController
 */
class CEAIController extends CController {

    /** @var CSenderHTTP */
    public static $sender_http;

    /**
     * @param RequestApi $request_api
     *
     * @return Response
     * @throws Exception
     * @api
     */
    public function receiveMessage(RequestApi $request_api): Response {
        $acq = CEAIDispatcher::dispatch($request_api->getContent(false, false), self::$sender_http);

        if (CEAIDispatcher::$errors) {
            throw new CEAIException(CEAIException::INVALID_DISPATCH, Response::HTTP_CONFLICT, CMbArray::get(CEAIDispatcher::$errors, 0));
        }

        $res = new Item(array('message' => $acq));
        $res->setType('Acquittement');

        return $this->renderApiResponse($res);
    }

    /**
     * @param Request $request
     * @param CUser   $user
     *
     * @return void
     * @throws Exception
     */
    public static function authenticateSender(Request $request, CUser $user): void
    {
        $sender = CSenderHTTP::loadFromUser($user);
        if (!$sender) {
            throw new CEAIException(CEAIException::INVALID_SENDER, Response::HTTP_PRECONDITION_FAILED);
        }

        self::$sender_http = $sender;
    }
}
