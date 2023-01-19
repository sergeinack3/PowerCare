<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;
use JsonSerializable;
use Ox\Core\Kernel\Kernel;
use Ox\Core\Kernel\Routing\RouterBridge;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Throwable;

/**
 * Class CControllerLegacy
 * @warning all public function are exposed as action : index.php?m=lorep&a=ipsum
 */
class CLegacyController
{
    /**
     * @param string $tpl
     * @param array $tpl_vars
     * @param string|null $dir
     * @param bool $fetch
     *
     * @return mixed
     */
    protected function renderSmarty(string $tpl, array $tpl_vars = [], string $dir = null, bool $fetch = false)
    {
        $smarty = new CSmartyDP($dir);

        if (!empty($tpl_vars)) {
            $smarty->assign($tpl_vars);
        }

        if ($fetch) {
            return $smarty->fetch($tpl);
        }

        $smarty->display($tpl);

        return null;
    }
    
    protected function renderEntryPoint(EntryPoint $entry_point): void
    {
        echo (new CSmartyDP())->mb_entry_point(['entry_point' => $entry_point]);
    }

    /**
     * @param array|scalar|JsonSerializable $data
     *
     * @throws Exception
     */
    protected function renderJson($data): void
    {
        if (
            is_array($data)
            || is_scalar($data)
            || $data instanceof JsonSerializable
        ) {
            CApp::json($data);
        }

        throw new Exception("Data must be of type: scalar, string, array, JsonSerializable");
    }

    protected function callAction(string $action): CHtml
    {
        if (!is_callable(static::class, $action)) {
            throw new CMbException('Invalid controller action : ' . static::class . '::' . $action);
        }

        ob_start();
        call_user_func([static::class, $action]);
        $content = ob_get_contents();
        ob_end_clean();

        return new CHtml($content);
    }

    protected function rip(): void
    {
        CApp::rip();
    }

    protected function checkPerm(): void
    {
        CCanDo::check();
    }

    protected function checkPermAdmin(): void
    {
        CCanDo::checkAdmin();
    }

    protected function checkPermEdit(): void
    {
        CCanDo::checkEdit();
    }

    protected function checkPermRead(): void
    {
        CCanDo::checkRead();
    }


    /**
     * @param mixed $var
     */
    protected function dump($var, string $msg = null): void
    {
        CApp::dump($var, $msg);
    }

    /**
     * @param mixed $var
     *
     * @throws Exception
     */
    protected function log($var, string $msg = null): void
    {
        CApp::log($var, $msg);
    }

    protected function getRootDir(): string
    {
        return dirname(__DIR__, 2);
    }

    protected function redirect(string $params = ""): void
    {
        CAppUI::redirect($params);
    }


    /**
     * Generate an URL using a $route_name.
     */
    protected function generateUrl(
        string $route_name,
        array $parameters = [],
        string $reference_type = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {

        return RouterBridge::generateUrl($route_name, $parameters, $reference_type);
    }
}
