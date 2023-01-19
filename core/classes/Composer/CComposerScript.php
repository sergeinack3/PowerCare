<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Composer;

use Composer\Installer\PackageEvent;
use Composer\Script\Event;
use Exception;
use Ox\Core\Autoload\CAutoloadAlias;
use Ox\Core\Cache;
use Ox\Core\CacheManager;
use Ox\Core\CClassMap;
use Ox\Core\CMbConfig;
use Ox\Core\CMbException;
use Ox\Core\Config\CConfigDist;
use Ox\Core\Kernel\Routing\RouteManager;
use Ox\Core\Kernel\Services\ServicesManager;
use Ox\Core\Libraries\CLibrary;
use Ox\Core\Version\Builder;

class CComposerScript
{
    /** @var string */
    private const AUTOLOAD_FILE = 'autoload.php';

    public static  $vendor_dir;
    public static  $root_dir;
    public static  $composer;
    public static  $event;
    public static  $is_running            = false;
    public static  $is_config_file_exists = false;
    private static $packages              = [];

    /**
     * @param Event $event
     * @param bool  $require_autoload
     * @param bool  $check_configuration
     */
    private static function init(Event $event, $require_autoload = false, $check_configuration = false)
    {
        static::$is_running = true;
        static::$event      = $event;
        static::$composer   = $event->getComposer();
        static::$vendor_dir = static::$composer->getConfig()->get('vendor-dir');
        static::$root_dir   = dirname(static::$vendor_dir);
        if ($require_autoload) {
            require static::$vendor_dir . DIRECTORY_SEPARATOR . static::AUTOLOAD_FILE;
        }

        if ($check_configuration) {
            $config = new CMbConfig(static::$root_dir);
            if (!$config->isConfigFileExists()) {
                static::$event->getIO()->write(
                    '<warning>OX configurations is missing, you should execute "composer ox-install-config"</warning>'
                );
            } else {
                static::$is_config_file_exists = true;
            }
        }
    }


    /**
     * @param Event $event
     */
    public static function preAutoloadDump(Event $event)
    {
        static::init($event);
        static::addPrefixPsr4();
    }

    public static function postPackageInstall(PackageEvent $event)
    {
        static::postPackageEvent($event);
    }

    public static function postPackageUpdate(PackageEvent $event)
    {
        static::postPackageEvent($event);
    }

    private static function postPackageEvent(PackageEvent $event)
    {
        $package_name       = static::getPackageName($event);
        static::$packages[] = $package_name;
    }

    /**
     * Returns the package name associated with $event
     *
     * @param PackageEvent $event Package event
     *
     * @return string
     */
    public static function getPackageName(PackageEvent $event)
    {
        /** @var InstallOperation|UpdateOperation $operation */
        $operation = $event->getOperation();

        $package = method_exists($operation, 'getPackage')
            ? $operation->getPackage()
            : $operation->getInitialPackage();

        return $package->getName();
    }


    /**
     * @param Event $event
     *
     * @return void
     * @throws Exception
     */
    public static function postAutoloadDump(Event $event)
    {
        static::init($event, false, true);
        static::buildOxClassMap();
        static::buildConfigDist();
        static::buildOxClassRef();
        static::buildOxLegacyActions();
        static::buildAllRoutes();
        static::buildOpenApiDocumentation();
        static::buildAllServices();
        static::buildVersion();
        static::buildLibjs();
        static::clearCache();
    }

    /**
     * @return void
     */
    private static function addPrefixPsr4(): void
    {
        $root_package = static::$composer->getPackage();
        $root         = str_replace('vendor', '', static::$vendor_dir);
        $composer     = new CComposer($root);
        $msg          = $composer->addPrefixPsr4FromModulesComposer($root_package);

        static::write($msg);
    }

    /**
     * @param Event $event
     *
     * @return void
     * @throws Exception
     */
    public static function updateRoutes(Event $event): void
    {
        static::init($event, true, true);
        static::buildAllRoutes();
        static::buildOpenApiDocumentation();
        static::clearCache();
    }

    /**
     * @param Event $event
     */
    public static function oxClearCache(Event $event)
    {
        static::init($event, true, true);
        static::clearCache();
    }

    /**
     *
     * @return void
     * @throws Exception
     */
    private static function buildOpenApiDocumentation(): void
    {
        $time_start = microtime(true);

        $manager = new RouteManager();
        $manager->loadAllRoutes();
        $documentation = $manager->convertRoutesApiToOAS();

        // Store
        $file = self::$root_dir . '/includes/documentation.yml';
        if (file_exists($file) && is_file($file)) {
            unlink($file);
        }
        file_put_contents($file, $documentation);

        $time = round(microtime(true) - $time_start, 3);

        static::write(
            "Generated openapi documentation file in {$file} during {$time} sec"
        );
    }

    /**
     *
     * @return void
     * @throws Exception
     */
    private static function buildOxClassMap(): void
    {
        Cache::init(static::$root_dir);

        $msg = CClassMap::getInstance()->buildClassMap();
        static::write($msg);
    }

    /**
     * @warrning need classmap.php && config_dist.php
     * @return void
     * @throws Exception
     */
    private static function buildOxClassRef(): void
    {
        $msg = CClassMap::getInstance()->buildClassRef();
        static::write($msg);
    }

    /**
     * @warrning need classmap.php
     * @return void
     * @throws Exception
     */
    private static function buildOxLegacyActions(): void
    {
        $msg = CClassMap::getInstance()->buildLegacyActions();
        static::write($msg);
    }

    /**
     *
     * @return void
     * @throws Exception
     */
    private static function buildAllRoutes(): void
    {
        $manager = new RouteManager();
        $msg     = $manager->loadAllRoutes()->buildAllRoutes();
        static::write($msg);
    }


    /**
     *
     * @return void
     * @throws Exception
     */
    private static function buildAllServices(): void
    {
        $manager = new ServicesManager();
        $msg     = $manager->buildAllServices();
        static::write($msg);
    }

    /**
     *
     * @return void
     * @throws Exception
     */
    private static function buildConfigDist(): void
    {
        $config_dist = new CConfigDist();
        $msg         = $config_dist->build();
        static::write($msg);
    }

    public static function buildVersion(): void
    {
        $msg = Builder::buildVersion();
        static::write($msg);
    }

    /**
     * @return void
     * @throws CMbException
     */
    private static function buildLibJs(): void
    {
        $msg = CLibrary::installAll();
        static::write($msg);
    }

    /**
     * @return void
     */
    private static function clearCache(): void
    {
        if (!static::$is_config_file_exists) {
            return;
        }

        // Start
        $time_start = microtime(true);

        // autoload alias
        CAutoloadAlias::register();

        // includes configs (legacy)
        require static::$root_dir . '/includes/config_all.php';

        // Init cache
        // Warning : we clear only cli SHM, it's just a mock for CacheManager::cacheClear
        Cache::init(static::$root_dir);

        // clear
        CacheManager::clearCache('all', CacheManager::SHM);
        $count = CacheManager::getCountTypes();
        $time  = @round(microtime(true) - $time_start, 3);
        static::write("Clearing cache containing {$count} types during {$time} sec");

        // restore
        unset($dPconfig);
        CAutoloadAlias::unregister();
    }

    /**
     * @param string $msg
     * @param string $type
     *
     * @return void
     */
    private static function write(string $msg, string $type = 'info'): void
    {
        $msg = "<{$type}>openxtrem/mediboard:</{$type}> $msg";
        static::$event->getIO()->write($msg);
    }

    /**
     * @param string $msg
     *
     * @return void
     */
    private static function warning(string $msg): void
    {
        static::write($msg, 'warning');
    }
}
