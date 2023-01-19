<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\Controllers;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CController;
use Ox\Core\Kernel\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FCController extends CController
{
    /**
     * @param Request $request
     *
     * @return Response
     * @throws Exception
     *
     * @throws NotFoundException
     * @api public
     */
    public function callback(Request $request): Response
    {
        if (!CAppUI::isLoginFCEnabled()) {
            return new Response(null, Response::HTTP_UNAUTHORIZED);
        }

        // Default redirect is login page (when signing out).
        $redirect_url = CAppUI::conf("external_url");

        if ($request->query->has('state') && $request->query->has('code')) {
            $redirect_url .= '?fc=1&' . $request->getQueryString();
        }

        return $this->redirect($redirect_url);
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws Exception
     * @api public
     */
    public function logout(Request $request): Response
    {
        if (!CAppUI::isLoginFCEnabled()) {
            return new Response(null, Response::HTTP_UNAUTHORIZED);
        }

        return $this->redirect(CAppUI::conf('external_url'));
    }
}
