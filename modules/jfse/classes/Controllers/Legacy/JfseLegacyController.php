<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers\Legacy;

use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Mediboard\Jfse\Responses\FileResponse;
use Ox\Mediboard\Jfse\Router;
use Ox\Mediboard\System\CSourceHTTP;
use Symfony\Component\HttpFoundation\JsonResponse;

class JfseLegacyController extends CLegacyController
{
    public function configure(): void
    {
        $this->checkPermAdmin();

        $this->renderSmarty('configure', [
            'source' => CSourceHTTP::get('JfseApi', 'http'),
        ]);
    }

    public function jfseIndex(): void
    {
        $this->checkPerm();

        $response = Router::handle();
        if ($response instanceof JsonResponse) {
            ob_clean();
            header("Content-Type: application/json");
        } elseif ($response instanceof FileResponse) {
            ob_clean();
            $response->sendHeaders();
        }

        echo $response->getContent();
    }

    public function displayMessage(): void
    {
        $this->checkPermRead();

        $message = CView::post('message', 'str notNull');
        $type    = CView::post('type', 'enum list|error|info|success|warning default|info');

        CView::checkin();

        $this->renderSmarty('inc_message', [
            'message' => stripslashes($message),
            'type'    => $type,
        ]);
    }

    public function displayMessages(): void
    {
        $this->checkPermRead();

        $messages = json_decode(stripslashes(utf8_encode(CView::post('messages', 'str'))), true);

        CView::checkin();

        $this->renderSmarty('inc_messages', [
            'messages' => array_map_recursive('utf8_decode', $messages),
        ]);
    }

    public function adminSettings(): void
    {
        $this->checkPermAdmin();

        $this->callRoute('gui/admin/settings');
    }

    public function cps(): void
    {
        $this->checkPerm();

        $this->callRoute('cps/index');
    }

    public function guiSettings(): void
    {
        $this->checkPermAdmin();

        $this->callRoute('gui/index');
    }

    public function manageStsFormula(): void
    {
        $this->checkPermAdmin();

        $this->callRoute('gui/formula/manage');
    }

    public function stats(): void
    {
        $this->checkPermRead();

        $this->callRoute('stats/index');
    }

    public function userManagement(): void
    {
        $this->checkPermAdmin();

        $this->callRoute('user_management/index');
    }

    public function importNoemiePayments(): void
    {
        $this->checkPermAdmin();

        $this->callRoute('noemie/importNoemiePayments');
    }

    public function importInvoiceAcknowledgements(): void
    {
        $this->checkPermAdmin();

        $this->callRoute('noemie/importInvoiceAcknowledgements');
    }

    private function callRoute(string $route): void
    {
        $response = Router::handle($route);
        if ($response instanceof JsonResponse) {
            ob_clean();
            header("Content-Type: application/json");
        }

        echo $response->getContent();
    }
}
