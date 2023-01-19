<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Kernel\Routing;

use Symfony\Component\HttpFoundation\Request;

/**
 * This trait is simple helper to provide accessor on request attribut
 * And to return custom info on request (public, api/gui ... )
 */
trait RequestHelperTrait
{
    /** @var Request */
    protected $request;

    protected function setRequest(Request $request)
    {
        $this->request = $request;
    }

    protected function getRequest(): ?Request
    {
        return $this->request;
    }

    /**
     * Tell whether the given request is in a API context.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isRequestApi(Request $request)
    {
        return strpos($request->getPathInfo(), 'api') === 1;
    }

    /**
     * Tell whether the given request is in a Gui context.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isRequestGui(Request $request)
    {
        return strpos($request->getPathInfo(), 'gui') === 1;
    }

    /**
     * Tell whether the given request is in a Gui or Api context.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isRequestApiOrGui(Request $request)
    {
        return $this->isRequestApi($request) || $this->isRequestGui($request);
    }

    protected function isEnvironementDev(Request $request)
    {
        return $request->server->get('APP_ENV') === "dev";
    }

    protected function isAppDebug(Request $request)
    {
        return $request->server->get('APP_DEBUG');
    }


    /**
     * Tell whether the given request is in a profiler context
     */
    protected function isRequestProfiler(Request $request): bool
    {
        return strpos($request->getPathInfo(), '_profiler') === 1;
    }

    /**
     * Tell whether the given request is in a public environment.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isRequestPublic(Request $request): bool
    {
        return $request->attributes->get('public') === true;
    }


    /**
     * Get the controller attributes
     *
     * @param Request $request
     *
     * @return string|null
     */
    protected function getController(Request $request): ?string
    {
        $_controller = $request->attributes->get('_controller', '');
        if (strpos($_controller, '::') !== false) {
            return explode('::', $_controller)[0];
        }

        return $_controller;
    }
}
