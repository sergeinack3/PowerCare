<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Controllers;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\CController;
use Ox\Mediboard\System\ViewSender\CViewSender;
use Symfony\Component\HttpFoundation\Response;

/**
 * API Controller for Monitor views
 */
class ViewSendersController extends CController
{
    /**
     * @throws ApiException
     * @throws Exception
     * @api
     */
    public function getViewSendersList(RequestApi $request_api): Response
    {
        $sender         = new CViewSender();
        $sender->active = 1;
        $senders        = $sender->loadMatchingListEsc("name");

        /** @var Collection $resource */
        $resource = Collection::createFromRequest($request_api, $senders);

        $resource->createLinksPagination(
            $request_api->getOffset(),
            $request_api->getLimit(),
            count($senders)
        );

        return $this->renderApiResponse($resource);
    }
}
