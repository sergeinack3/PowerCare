<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use DirectoryIterator;
use Exception;
use Ox\Components\Cache\Exceptions\CouldNotGetCache;
use Ox\Core\Kernel\Routing\RouterBridge;
use Ox\Core\Logger\Wrapper\ApplicationLoggerWrapper;
use Ox\Core\Module\AbstractModuleCache;
use Ox\Core\Module\CModule;
use Ox\Mediboard\System\CConfigurationModelManager;
use Ox\Mediboard\System\ConfigurationException;
use Ox\Mediboard\System\ConfigurationManager;
use Ox\Mediboard\System\Controllers\PreferencesController;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

/**
 * Todo: Replace with Cache Tagging.
 *
 * Cache manager class
 */
class CacheManager
{
    public const SHM     = 1;
    public const DSHM    = 2;
    public const SPECIAL = 4;

    public const SHM_SPECIAL  = self::SHM | self::SPECIAL;
    public const DSHM_SPECIAL = self::DSHM | self::SPECIAL;

    public const ALL = self::SHM | self::DSHM | self::SPECIAL;

    private static string $module_cache_class = AbstractModuleCache::class;

    private static int $types = 0;

    public static array $cache_values = [
        'all'               => true,
        'css'               => false,
        'js'                => false,
        'config'            => false,
        'locales'           => false,
        'logs'              => false,
        'templates'         => false,
        'devtools'          => false,
        'children'          => false,
        'core'              => false,
        'routing'           => false,
        'modules'           => false,
    ];

    private static array $outputs = [];


    /**
     * @param string $msg
     * @param int    $type
     * @param mixed  ...$args
     */
    public static function output(string $msg, int $type = CAppUI::UI_MSG_OK, ...$args): void
    {
        static::$types++;

        self::$outputs[] = [
            "msg"  => $msg,
            "type" => $type,
            "args" => $args,
        ];
    }

    public static function getOutputs(): string
    {
        foreach (self::$outputs as $output) {
            $msg  = $output['msg'];
            $type = $output['type'];
            $args = $output['args'];

            CAppUI::setMsg($msg, $type, ...$args);
        }

        self::$outputs = [];

        return CAppUI::getMsg(true);
    }

    public static function getCountTypes(): int
    {
        return static::$types;
    }

    /**
     * @throws InvalidArgumentException
     * @throws CouldNotGetCache
     */
    private static function clearModuleActionCache(): void
    {
        Cache::deleteKeys(Cache::OUTER, 'CModuleAction.getID-');
    }

    /**
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     */
    private static function clearTabsCache(): void
    {
        /* Register tabs removal */
        Cache::deleteKeys(Cache::OUTER, 'CModule.registerTabs');
        /* Show module infos */
        Cache::deleteKeys(Cache::OUTER, 'SystemController.showModule');
    }

    /**
     * @throws InvalidArgumentException
     * @throws CouldNotGetCache
     */
    public static function clearLegacyControllerCache(): void
    {
        Cache::deleteKeys(Cache::OUTER, 'CModule.matchLegacyController');
        static::output("legacy-controller-cache-removed", CAppUI::UI_MSG_OK);
    }

    /**
     * Clears Locales Cache
     *
     * @return void
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     */
    private static function clearLocalesCache(): void
    {
        $cache = Cache::getCache(Cache::OUTER);

        /* Remove locales, at the end because otherwise, next message aren't translated */
        foreach (glob("locales/*", GLOB_ONLYDIR) as $localeDir) {
            $localeName = basename($localeDir);
            $sharedName = "locales-$localeName";

            if (!$cache->get("$sharedName-" . CAppUI::LOCALES_PREFIX)) {
                static::output("Locales-shm-none", CAppUI::UI_MSG_OK, $localeName);
                continue;
            }

            if (!Cache::deleteKeys(Cache::OUTER, "$sharedName-")) {
                static::output("Locales-shm-rem-ko", CAppUI::UI_MSG_WARNING, $localeName);
                continue;
            }

            static::output("Locales-shm-rem-ok", CAppUI::UI_MSG_OK, $localeName);
        }
    }

    /**
     * Clears Config Cache
     *
     * @throws CMbException
     * @throws ConfigurationException
     */
    private static function clearConfigCache(): void
    {
        $manager = ConfigurationManager::get();

        foreach (CModule::getInstalled() as $_mod) {
            CConfigurationModelManager::clearCache($_mod->mod_name);
            $manager->clearCache($_mod->mod_name);
        }

        static::output("ConfigValues-shm-rem-ok", CAppUI::UI_MSG_OK);
    }

    /**
     * @throws InvalidArgumentException
     * @throws CouldNotGetCache
     */
    private static function clearPreferencesCache(): void
    {
        foreach (CModule::getInstalled() as $_mod) {
            $cache = new Cache(PreferencesController::CACHE_PREFIX, $_mod->mod_name, Cache::INNER_OUTER);
            if ($cache->exists()) {
                $cache->rem();
            }
        }

        $cache = new Cache(
            PreferencesController::CACHE_PREFIX,
            PreferencesController::NO_MODULE_PREF_NAME,
            Cache::INNER_OUTER
        );
        if ($cache->exists()) {
            $cache->rem();
        }
    }

    /**
     * @description Clears JS Cache
     */
    private static function clearJavascriptCache(): void
    {
        $js_files = glob("tmp/*.js");
        foreach ($js_files as $_js_file) {
            unlink($_js_file);
        }
        static::output("JS-cache-ok", CAppUI::UI_MSG_OK, count($js_files));
    }

    /**
     * @description Clears CSS Cache
     */
    private static function clearStylesheetsCache(): void
    {
        $css_files = glob("tmp/*.css");
        foreach ($css_files as $_css_file) {
            unlink($_css_file);
        }
        static::output("CSS-cache-ok", CAppUI::UI_MSG_OK, count($css_files));
    }

    /**
     * @description Clears Logs Cache
     */
    private static function clearLogsCache(): void
    {
        $file_log  = ApplicationLoggerWrapper::getPathApplicationLog();
        $file_grep = str_replace(".log", ".grep.log", $file_log);
        if (file_exists($file_grep)) {
            unlink($file_grep);
        }
        static::output("Log-grep-cache-ok", CAppUI::UI_MSG_OK);
    }

    /**
     * @description Clears devtools Cache
     */
    private static function clearDevtoolsCache(): void
    {
        $dir = substr(CDevtools::PATH_TMP, 1, -1);
        if (is_dir($dir)) {
            $files = glob($dir . '/*');
            foreach ($files as $file) {
                unlink($file);
            }
            rmdir($dir);
        }
        static::output("devtools-cache-removed", CAppUI::UI_MSG_OK);
    }

    /**
     * @description Clears Symfony Cache (only RouterBridge)
     * @todo        clear /var ?
     */
    private static function clearSymfonyCache(): void
    {
        $dir = substr(RouterBridge::CACHE_DIR, 1, -1);
        if (is_dir($dir)) {
            $files = glob($dir . '/*');
            foreach ($files as $file) {
                unlink($file);
            }
            rmdir($dir);
        }
        static::output("symfony-cache-removed", CAppUI::UI_MSG_OK);
    }

    /**
     * @description Clears Templates Cache
     */
    private static function clearTemplatesCache(): void
    {
        /* DO NOT use CMbPath::removed because it must be used in the installer */
        $templates_path = dirname(__DIR__, 2) . '/tmp/templates_c';

        if (is_dir($templates_path)) {
            static::emptyDir($templates_path);
            static::output("template-cache-removed", CAppUI::UI_MSG_OK);
        } else {
            static::output("template-cache-empty", CAppUI::UI_MSG_OK);
        }
    }

    /**
     * Empty a directory by recursivly deleting files and emptying child directories.
     */
    private static function emptyDir(string $path): void
    {
        $it = new DirectoryIterator($path);

        /** @var DirectoryIterator $directory */
        foreach ($it as $directory) {
            if ($directory->valid() && !$directory->isDot()) {
                if ($directory->isFile()) {
                    unlink($directory->getPathname());
                } elseif ($directory->isDir()) {
                    $dir_path = $directory->getPathname();
                    static::emptyDir($dir_path);
                    rmdir($dir_path);
                }
            }
        }
    }

    /**
     * Clears Classmap Cache
     *
     * @return void
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     */
    private static function clearChildClasses(): void
    {
        // Todo: Does not return number of deleted keys.
        $nb = Cache::deleteKeys(Cache::OUTER, "CApp.getChildClasses");
        static::output("Children-cache-ok", CAppUI::UI_MSG_OK, $nb);
    }

    /**
     * Clear Core Cache
     *
     * @return void
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     */
    private static function clearCoreCache(): void
    {
        // Todo: Does not return number of deleted keys.
        $nb = Cache::deleteKeys(Cache::OUTER, 'CCSSLoader');
        static::output("CSS-list-cache-ok", CAppUI::UI_MSG_OK, $nb);

        // Todo: Does not return number of deleted keys.
        $nb = Cache::deleteKeys(Cache::OUTER, 'CConfiguration');
        static::output("CConfiguration-list-cache-ok", CAppUI::UI_MSG_OK, $nb);

        // Todo: Does not return number of deleted keys.
        $nb = Cache::deleteKeys(Cache::OUTER, 'CModelObject');
        static::output("CModelObject-list-cache-ok", CAppUI::UI_MSG_OK, $nb);
    }

    /**
     * Clear DSN Cache
     *
     * @return void
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     */
    private static function clearDSNCache(): void
    {
        // Todo: Does not return number of deleted keys.
        $nb = Cache::deleteKeys(Cache::OUTER, 'CSQLDataSource');
        static::output("Datasource-list-cache-ok", CAppUI::UI_MSG_OK, $nb);
    }

    /**
     * Clear object indexer cache
     * @return void
     */
    private static function clearIndexCache(): void
    {
        $nb = CObjectIndexer::removeIndexes();
        static::output("Index-list-cache-ok", CAppUI::UI_MSG_OK, $nb);
    }

    /**
     * Returns an array of class names
     *
     * @return array|bool
     */
    public static function getModuleCacheClasses()
    {
        try {
            return CClassMap::getInstance()->getClassChildren(self::$module_cache_class);
        } catch (Exception $e) {
            static::output($e->getMessage(), CAppUI::UI_MSG_WARNING);

            return false;
        }
    }

    /**
     * @throws ConfigurationException
     * @throws InvalidArgumentException
     * @throws CouldNotGetCache
     * @throws CMbException
     * @throws ReflectionException
     */
    public static function clearCache(string $cache_key, int $layer): void
    {
        switch ($cache_key) {
            case 'all':
                self::clearAllCache($layer);
                break;

            case 'locales':
                self::clearLocalesCache();
                break;

            case 'css':
                self::clearStylesheetsCache();
                break;

            case 'js':
                self::clearJavascriptCache();
                break;

            case 'templates':
                self::clearTemplatesCache();
                break;

            case 'devtools':
                self::clearDevtoolsCache();
                break;

            case 'config':
                self::clearConfigCache();
                break;

            case 'logs':
                self::clearLogsCache();
                break;

            case 'children':
                self::clearChildClasses();
                break;

            case 'core':
                self::clearCoreCache();
                break;

            case 'modules':
                self::clearModulesCache($cache_key, $layer);
                self::clearTabsCache();
                break;

            case 'routing':
                self::clearLegacyControllerCache();
                self::clearSymfonyCache();
                break;

            default:
                self::clearModuleCache(stripcslashes($cache_key), $layer);
        }
    }

    /**
     * @param string $cache_key
     * @param int    $layer
     *
     * @return void
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    private static function clearModulesCache(string $cache_key, int $layer): void
    {
        if ($cache_key === 'all') {
            /* Remove modules cache */
            $cache = new Cache('CModule', 'all', Cache::INNER_OUTER);
            if (!$cache->get()) {
                static::output("Modules-shm-none", CAppUI::UI_MSG_WARNING);
            } else {
                $cache->rem();
                static::output("Modules-shm-none", CAppUI::UI_MSG_OK);
            }

            $cache = new Cache('CModule.exists', 'all', Cache::INNER_OUTER);
            $cache->rem();

            // Clear module action cache
            self::clearModuleActionCache();
        }

        /* Module specific removals */
        $module_cache_classes = self::getModuleCacheClasses();

        if (
            ($cache_key === 'all' || $cache_key === 'modules')
            && (is_array($module_cache_classes) && count($module_cache_classes))
        ) {
            foreach ($module_cache_classes as $module_cache_class) {
                self::clearModuleCache($module_cache_class, $layer);
            }
        }
    }

    /**
     * @throws ReflectionException
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     */
    private static function clearModuleCache(string $module_cache_class, int $layer): void
    {
        if (!class_exists($module_cache_class)) {
            return;
        }

        /** @var AbstractModuleCache $module_cache */
        $module_cache = new $module_cache_class();

        if (is_subclass_of($module_cache, self::$module_cache_class, true)) {
            $module_cache->clear($layer);
        }
    }

    /**
     * @param int $layer
     *
     * @return void
     * @throws CMbException
     * @throws ConfigurationException
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public static function clearAllCache(int $layer): void
    {
        if ($layer & self::SHM) {
            self::clearDevtoolsCache();
            self::clearSymfonyCache();
            self::clearTemplatesCache();
            self::clearTabsCache();
            self::clearLegacyControllerCache();
            self::clearChildClasses();
            self::clearLocalesCache();
            self::clearConfigCache();
            self::clearPreferencesCache();
            self::clearJavascriptCache();
            self::clearStylesheetsCache();
            self::clearLogsCache();
            self::clearModulesCache('all', $layer);
            self::clearCoreCache();
            self::clearDSNCache();
            self::clearIndexCache();
        }

        if ($layer & self::DSHM) {
            self::clearModulesCache('all', $layer);
        }
    }

    public static function formatLayer(int $layer): string
    {
        $formatted = [];

        if ($layer & self::SHM) {
            $formatted[] = 'SHM';
        }

        if ($layer & self::DSHM) {
            $formatted[] = 'DSHM';
        }

        if ($layer & self::SPECIAL) {
            $formatted[] = 'SPECIAL';
        }

        return implode("+", $formatted);
    }
}
