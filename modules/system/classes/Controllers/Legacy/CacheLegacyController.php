<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Controllers\Legacy;

use Exception;
use Ox\Components\Cache\Exceptions\CouldNotGetCache;
use Ox\Core\Cache;
use Ox\Core\CacheManager;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\Module\AbstractModuleCache;
use Ox\Core\Module\Cache\CacheCleanerStrategyFactory;
use Ox\Core\Sessions\CSessionManager;

class CacheLegacyController extends CLegacyController
{
    public function view_cache(): void
    {
        $this->checkPermAdmin();

        $modules_cache = [];
        $dshm_infos    = [];
        $cache_keys    = CacheManager::$cache_values;
        $cache_layers  = [
            "outer" => "shm",
            "distr" => "dshm",
        ];
        $servers_ip    = preg_split("/\s*,\s*/", CAppUI::conf("servers_ip"), -1, PREG_SPLIT_NO_EMPTY);

        // DSHM infos
        try {
            $cache      = Cache::getCache(Cache::DISTR);
            $dshm_infos = [
                'name'    => $cache->getMetadata()->get(Cache::DISTR, 'engine'),
                'version' => $cache->getMetadata()->get(Cache::DISTR, 'engine_version'),
            ];
        } catch (CouldNotGetCache $e) {
        }

        // Get module cache classes
        try {
            $module_cache_classes = CacheManager::getModuleCacheClasses();
            foreach ($module_cache_classes as $module_cache_class) {
                /** @var AbstractModuleCache $module_cache */
                $module_cache = new $module_cache_class();

                $modules_cache[$module_cache->getModuleName()] = [
                    "class" => $module_cache_class,
                    "distr" => $module_cache->getDSHMPatterns(),
                    "outer" => $module_cache->getSHMPatterns(),
                ];
            }
        } catch (Exception $e) {
            CAppUI::displayAjaxMsg($e->getMessage(), UI_MSG_WARNING);
        }

        $this->renderSmarty(
            'view_cache',
            [
                "cache_layers"  => $cache_layers,
                "cache_keys"    => $cache_keys,
                "modules_cache" => $modules_cache,
                "servers_ip"    => $servers_ip,
                "dshm_infos"    => $dshm_infos,
                "actual_ip"     => $_SERVER["SERVER_ADDR"],
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function clear(): void
    {
        $this->checkPermAdmin();

        $cache        = CView::get('cache', 'str');
        $target       = CView::get('target', 'str');
        $layer        = CView::get('layer', 'enum list|shm|dshm|all');
        $layer_strict = (int)CView::get('layer_strict', 'num');

        CView::checkin();

        // If layer_strict is passed from a remote strategy, layer need to be the same,
        // else, convert layer (string) to layer (int).
        if ($layer_strict > 0) {
            $layer = $layer_strict;
        } else {
            switch ($layer) {
                case 'shm':
                    $layer = CacheManager::SHM_SPECIAL;
                    break;

                case 'dshm':
                    $layer = CacheManager::DSHM_SPECIAL;
                    break;

                case 'all':
                default:
                    $layer = CacheManager::ALL;
            }
        }

        $hosts        = explode(',', trim(CAppUI::conf("servers_ip")));
        $actual_host  = $_SERVER["SERVER_ADDR"];
        $session_name = CSessionManager::forgeSessionName();
        $cookie       = CValue::cookie($session_name);

        $strategy = (new CacheCleanerStrategyFactory($cache, $target, $layer))
            ->withHosts($hosts)
            ->withCookie("{$session_name}={$cookie}")
            ->withActualHost($actual_host)
            ->create();

        $strategy->execute();

        // Outputs
        $this->renderSmarty(
            'view_cache_result',
            [
                "result" => $strategy->getHtmlResult(new CSmartyDP()),
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function ajax_show_modal_clear_cache(): void
    {
        $this->checkPermAdmin();

        $cache  = CView::get('cache', 'str');
        $target = CView::get('target', 'str');
        $layer  = CView::get('layer', 'enum list|shm|dshm|all');
        $module = CView::get('module', 'str');
        $keys   = CView::get('keys', 'str');

        CView::checkin();

        $this->renderSmarty(
            'inc_view_cache_modal',
            [
                'cache'  => $cache,
                'target' => $target,
                'layer'  => $layer,
                'module' => $module,
                'keys'   => $keys,
            ]
        );
    }
}
