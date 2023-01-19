<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Controllers;

use Exception;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CController;
use Ox\Mediboard\Admin\CUser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profiler;

class SystemController extends CController
{

    /**
     * @throws Exception
     */
    public function offline(string $message): Response
    {
        $external_url = CApp::getBaseUrl();
        $base_href    = str_ends_with($external_url, '/') ? $external_url : $external_url . '/';

        $root_dir  = $this->getRootDir();
        $path      = "./images/pictures";
        $bg_custom = "./images/pictures/bg_custom.jpg";

        $vars     = [
            "bg_custom"   => $bg_custom,
            "bg"          => is_file($bg_custom),
            "src_logo"    => (file_exists(
                "$root_dir/$path/logo_custom.png"
            ) ? "$path/logo_custom.png" : "$path/logo.png"),
            "message"     => $message,
            "application" => CAppUI::conf("product_name"),
            "base_href"   => $base_href,
        ];
        $response = $this->render('offline.html.twig', $vars);

        $headers = [
            "Retry-After"  => 300,
            "Content-Type" => "text/html; charset=iso-8859-1",
        ];
        $response->headers->add($headers);

        return $response;
    }

    /**
     * @api public
     */
    public function status()
    {
        [$header, $status] = explode(':', CApp::getProxyHeader());

        $resource = new Item(
            [
                'status'  => $status,
                'version' => (string)CApp::getVersion(),
                'release' => CApp::getVersion()->getCode() ?? 'undefined',
                'date'    => CApp::getVersion()->getCompleteDate() ?? 'undefined',
            ]
        );

        $resource->setType('api_status');

        return $this->renderApiResponse($resource, 200, [$header => $status]);
    }

    /**
     * @return Response
     * @throws Exception
     * @api public
     */
    public function openapi(?Profiler $profiler)
    {
        if (null !== $profiler) {
            // if it exists, disable the profiler for this particular controller action
            $profiler->disable();
        }
        return $this->render("@system/swagger.html.twig", [
            "documentation" => "/../../includes/documentation.yml",
        ]);
    }

    /**
     * @return Response
     * @throws Exception
     */
    public function about()
    {
        // todo remove when authv3 merged
        if (!CUser::get()) {
            throw new Exception('Authentication faild');
        }

        return $this->render("@system/about.html.twig", ['version' => CApp::getVersion()]);
    }
}
