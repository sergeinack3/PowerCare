<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Module\Cache;

use Exception;
use InvalidArgumentException;
use Ox\Core\CacheManager;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Clear specific server SHM cache.
 */
class RemoteCacheCleanerStrategy implements CacheCleanerStrategyInterface
{
    private string              $cache;
    private int                 $layer;
    private HttpClientInterface $client;
    private string              $target;
    private string              $cookie;
    private string              $response;
    private ?string             $error = null;

    /**
     * @param string              $cache  Cache key to clear
     * @param int                 $layer  SHM or DSHM layer to clear (see CacheManager)
     * @param HttpClientInterface $client HTTClient for request execution
     * @param string              $target Targetted address
     * @param string              $cookie Cookie for authentication
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $cache, int $layer, HttpClientInterface $client, string $target, string $cookie)
    {
        // Cookie is mandatory for authentication in request
        if ($cookie === '') {
            throw new InvalidArgumentException(CAppUI::tr('system-msg-Cookie is missing'));
        }

        if ($target === '') {
            throw new InvalidArgumentException(CAppUI::tr('system-msg-Target is missing'));
        }

        $this->cache  = $cache;
        $this->layer  = $layer;
        $this->client = $client;
        $this->target = $target;
        $this->cookie = $cookie;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function execute(): void
    {
        $this->log();

        $options = [
            "query"        => [
                'cache'        => stripcslashes($this->cache),
                'layer_strict' => $this->layer,
                'target'       => 'local',
            ],
            "headers"      => [
                'Cookie' => $this->cookie,
            ],
            'timeout'      => 3,
            'max_duration' => 3,
        ];

        // Request in HTTP and not HTTPS because of legacy behavior
        $response = $this->client->request(
            'GET',
            "http://{$this->target}/index.php?m=system&raw=clear",
            $options
        );

        try {
            $this->response = $response->getContent();
        } catch (Exception $e) {
            $this->error = $e->getMessage();
        }
    }

    public function getHtmlResult(CSmartyDP $smarty = null): string
    {
        if ($this->error === null) {
            return $this->response;
        }

        if ($smarty === null) {
            throw new InvalidArgumentException();
        }

        $smarty->assign(
            [
                'outputs'     => $this->error,
                'actual_host' => $this->target,
            ]
        );

        return $smarty->fetch('view_cache_header');
    }

    /**
     * @throws Exception
     */
    private function log(): void
    {
        CApp::log(
            sprintf(
                'Clearing [%s] cache [%s] for [%s]',
                CacheManager::formatLayer($this->layer),
                $this->cache,
                $this->target
            )
        );
    }
}
