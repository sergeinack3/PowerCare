<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Kernel\Routing;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;

/**
 * RouterBridge is a standalone SF router
 * used for legacy application who need generate V2 url
 */
class RouterBridge extends Router
{

    /** @var string */
    public const CACHE_DIR = '/tmp/cache_sf/';

    /** @var string */
    public const RESOURCE = 'includes/all_routes.yml';

    /** @var RouterBridge */
    private static $instance;

    /**
     * CRouter constructor.
     */
    public function __construct()
    {
        $root_dir = dirname(__DIR__, 4);

        $fileLocator = new FileLocator($root_dir);

        $loader = new YamlFileLoader($fileLocator);

        $options = [
            'cache_dir' => $root_dir . static::CACHE_DIR,
        ];

        $request = Request::createFromGlobals();
        $context = new RequestContext($request->getBasePath());

        parent::__construct($loader, static::RESOURCE, $options, $context);

        self::$instance = $this;
    }


    /**
     * @return RouterBridge
     */
    public static function getInstance(): RouterBridge
    {
        if (is_null(self::$instance)) {
            self::$instance = new RouterBridge();
        }

        return self::$instance;
    }

    /**
     * @param             $name
     * @param array       $parameters
     * @param int         $referenceType
     * @param string|null $base_url
     *
     * @return string
     */
    public static function generateUrl(
        $name,
        array $parameters = [],
        int $referenceType = self::ABSOLUTE_PATH,
        string $base_url = null
    ): string {
        $url_generator = self::getInstance()->getGenerator();
        if ($base_url) {
            $base_url = rtrim($base_url, "/");
            $url_generator->setContext(new RequestContext($base_url));
        }

        return $url_generator->generate($name, $parameters, $referenceType);
    }
}
