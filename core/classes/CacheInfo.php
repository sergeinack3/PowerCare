<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Ox\Mediboard\System\CRedisServer;

/**
 * Cache information class
 */
class CacheInfo
{
    /**
     * Retrieves PHP opcode cache information
     *
     * @return array
     */
    public static function getOpcodeCacheInfo()
    {
        $root = dirname(realpath(__DIR__));

        $opcache_info = [
            "engine"     => "none",
            "version"    => null,

            // Global
            "total"      => 0,
            "used"       => 0,
            "total_rate" => 0,

            "free"       => 0,
            "wasted"     => 0,
            "start_time" => 0,

            "instance_size"  => 0,
            "instance_count" => 0,

            // Scripts
            "entries"        => 0,
            "hits"           => 0,
            "misses"         => 0,
            "hit_rate"       => 0,

            "entries_by_prefix" => [],
        ];

        /* ------- Zend OPcache -------  */
        if (extension_loaded("Zend OPcache") && ini_get("opcache.enable")) {
            $opcache_info["engine"]  = "OPcache";
            $opcache_info["version"] = phpversion("Zend OPcache");

            $opcache_status = opcache_get_status(true);
            $opcache_config = opcache_get_configuration();

            //$isb  = $opcache_status["interned_strings_usage"];
            $memory  = $opcache_status["memory_usage"];
            $stat    = $opcache_status["opcache_statistics"];
            $scripts = isset($opcache_status["scripts"]) ? $opcache_status["scripts"] : [];

            $hit_rate = 0;
            if (intval($stat["hits"] + $stat["misses"]) > 0) {
                $hit_rate = round(($stat["hits"] / ($stat["misses"] + $stat["hits"])) * 100, 2);
            }

            // Global
            $opcache_info["total"]      = $opcache_config["directives"]["opcache.memory_consumption"];
            $opcache_info["used"]       = $memory["used_memory"];
            $opcache_info["total_rate"] = 100 * $memory["used_memory"] / $opcache_config["directives"]["opcache.memory_consumption"];

            $opcache_info["free"]       = $memory["free_memory"];
            $opcache_info["wasted"]     = $memory["wasted_memory"];
            $opcache_info["start_time"] = $stat["start_time"];

            $opcache_info["instance_size"]  = 0;
            $opcache_info["instance_count"] = 0;

            // Interned string buffer
            /*"isb_total"     => $isb["buffer_size"],
            "isb_used"      => $isb["used_memory"],
            "isb_free"      => $isb["free_memory"],
            "isb_count"     => $isb["number_of_strings"],*/

            // Scripts
            $opcache_info["entries"]  = $stat["num_cached_scripts"];
            $opcache_info["hits"]     = $stat["hits"];
            $opcache_info["misses"]   = $stat["misses"];
            $opcache_info["hit_rate"] = $hit_rate;

            $entries_by_prefix = [];
            $prefix            = preg_replace('/[^\w]+/', "_", CAppUI::conf("root_dir"));

            foreach ($scripts as $_key => $_info) {
                if (strpos($_key, $root) !== 0) {
                    continue;
                }

                $opcache_info["instance_size"] += $_info["memory_consumption"];
                $opcache_info["instance_count"]++;

                $_unprefixed = ltrim(substr($_key, strlen($prefix) + 1), "\\/");

                $_pos = strpos($_unprefixed, "\\");
                if ($_pos !== false) {
                    $_prefix = substr($_unprefixed, 0, $_pos + 1);
                } else {
                    $_prefix = $_unprefixed;
                }

                if (!isset($entries_by_prefix[$_prefix])) {
                    $entries_by_prefix[$_prefix] = [
                        "count" => 0,
                        "size"  => 0,
                    ];
                }

                $entries_by_prefix[$_prefix]["count"]++;
                $entries_by_prefix[$_prefix]["size"] += $_info["memory_consumption"];
            }

            $opcache_info["entries_by_prefix"] = $entries_by_prefix;

            return $opcache_info;
        }

        /* ------- APC-------  */
        if (extension_loaded("apc") && !extension_loaded("apcu") && ini_get("apc.enabled")) {
            $opcache_info["engine"]  = "APC";
            $opcache_info["version"] = phpversion("apc");

            $opcache_status = apc_cache_info();
            $mem            = apc_sma_info();

            $hit_rate = 0;
            if (intval($opcache_status["num_hits"] + $opcache_status["num_misses"]) > 0) {
                $hit_rate = round(
                    ($opcache_status["num_hits"] / ($opcache_status["num_misses"] + $opcache_status["num_hits"])) * 100,
                    2
                );
            }

            // Global
            $opcache_info["total"]      = CMbString::fromDecaBinary(ini_get("apc.shm_size"));
            $opcache_info["used"]       = $mem['num_seg'] * $mem['seg_size'];
            $opcache_info["total_rate"] = 100 * $opcache_info["used"] / $opcache_info["total"];

            $opcache_info["free"]       = $mem['avail_mem'];
            $opcache_info["wasted"]     = null;
            $opcache_info["start_time"] = null;

            $opcache_info["instance_size"]  = 0;
            $opcache_info["instance_count"] = 0;

            // Scripts
            $opcache_info["entries"]  = count($opcache_info["cache_list"]);
            $opcache_info["hits"]     = $opcache_status["num_hits"];
            $opcache_info["misses"]   = $opcache_status["num_misses"];
            $opcache_info["hit_rate"] = $hit_rate;

            $entries_by_prefix = [];
            $prefix            = preg_replace('/[^\w]+/', "_", CAppUI::conf("root_dir"));

            foreach ($opcache_info["cache_list"] as $_info) {
                $_key = $_info["filename"];
                if (strpos($_key, $root) !== 0) {
                    continue;
                }

                $opcache_info["instance_size"] += $_info["mem_size"];
                $opcache_info["instance_count"]++;

                $_unprefixed = ltrim(substr($_key, strlen($prefix) + 1), "\\/");

                $_pos = strpos($_unprefixed, "\\");
                if ($_pos !== false) {
                    $_prefix = substr($_unprefixed, 0, $_pos + 1);
                } else {
                    $_prefix = $_unprefixed;
                }

                if (!isset($entries_by_prefix[$_prefix])) {
                    $entries_by_prefix[$_prefix] = [
                        "count" => 0,
                        "size"  => 0,
                    ];
                }

                $entries_by_prefix[$_prefix]["count"]++;
                $entries_by_prefix[$_prefix]["size"] += $_info["mem_size"];
            }

            $opcache_info["entries_by_prefix"] = $entries_by_prefix;

            return $opcache_info;
        }

        return $opcache_info;
    }

    /**
     * Retrieves PHP opcode cache information
     *
     * @param string $prefix Prefix to match
     *
     * @return array
     */
    public static function getOpcodeKeysInfo($prefix = null)
    {
        $root   = rtrim(str_replace("\\", "/", dirname(realpath(__DIR__))), "/");
        $prefix = rtrim(str_replace("\\", "/", $prefix), "/");

        $entries = [];

        /* ------- APC-------  */
        if (extension_loaded("apc") && !extension_loaded("apcu") && ini_get("apc.enabled")) {
            $opcache_status = apc_cache_info();

            foreach ($opcache_status["cache_list"] as $_info) {
                $_key = $_info["filename"];

                if (strpos($_key, "$root/$prefix") !== 0) {
                    continue;
                }

                $_unrooted = ltrim(substr($_key, strlen($root) + 1), "\\/");

                $entries[] = $_info;
            }
        }

        /* ------- Zend OPcache -------  */
        if (extension_loaded("Zend OPcache") && ini_get("opcache.enable")) {
            $opcache_status = opcache_get_status(true);
            $scripts        = $opcache_status["scripts"];

            foreach ($scripts as $_key => $_info) {
                $_key    = str_replace("\\", "/", $_key);
                $_prefix = "$root/$prefix";

                if (strpos($_key, $_prefix) !== 0) {
                    continue;
                }

                $_subkey = substr($_key, strlen($_prefix) + 1);

                $_entry = [
                    "ctime"     => $_info["timestamp"] ? CMbDT::strftime(CMbDT::ISO_DATETIME, $_info["timestamp"]) : null,
                    "mtime"     => $_info["timestamp"] ? CMbDT::strftime(CMbDT::ISO_DATETIME, $_info["timestamp"]) : null,
                    "atime"     => CMbDT::strftime(CMbDT::ISO_DATETIME, $_info["last_used_timestamp"]),
                    "size"      => $_info["memory_consumption"],
                    "hits"      => $_info["hits"],
                    "ttl"       => null,
                    "ref_count" => null,
                ];

                $entries[$_subkey] = $_entry;
            }
        }

        ksort($entries);

        return $entries;
    }

    /**
     * Retrieves assets (JS, CSS) cache information
     *
     * @return array
     */
    public static function getAssetsCacheInfo()
    {
        $tmp = realpath(__DIR__ . "/../../tmp");

        $info = [
            "versionKey" => CApp::getVersion()->getKey(),
            "css"        => [],
            "css_total"  => 0,
            "js"         => [],
            "js_total"   => 0,
        ];

        $css_files = glob("$tmp/*.css");
        foreach ($css_files as $_file) {
            $_entry = [
                "name" => basename($_file),
                "size" => filesize($_file),
                "date" => CMbDT::strftime(CMbDT::ISO_DATETIME, filemtime($_file)),
            ];

            $info["css_total"] += $_entry["size"];

            $info["css"][] = $_entry;
        }

        $js_files = glob("$tmp/*.js");
        foreach ($js_files as $_file) {
            $_entry = [
                "name" => basename($_file),
                "size" => filesize($_file),
                "date" => CMbDT::strftime(CMbDT::ISO_DATETIME, filemtime($_file)),
            ];

            $info["js_total"] += $_entry["size"];

            $info["js"][] = $_entry;
        }

        return $info;
    }

    public static function getDiskInfo(string $directory): array
    {
        $total   = disk_free_space($directory);
        $entries = array_fill_keys(scandir($directory), null);

        $total_size = 0;
        foreach ($entries as $_file => $_size) {
            $_filesize       = filesize($directory . $_file);
            $entries[$_file] = $_filesize;
            $total_size      += $_filesize;
        }

        $shm_global_info = [
            "_all_"   => [],
            "engine"  => 'Disk',
            "version" => 'N/A',

            "hits"     => null,
            "misses"   => null,
            "hit_rate" => null,

            "entries"    => count($entries),
            "expunges"   => null,
            "start_time" => null,

            "used"       => $total_size,
            "total"      => $total,
            "total_rate" => 100 * $total_size / $total,

            "instance_count" => 0,
            "instance_size"  => 0,
        ];

        $prefix = preg_replace('/[^\w]+/', "_", CAppUI::conf("root_dir"));

        $instance_count = 0;
        $instance_size  = 0;

        $shm_entries_by_prefix = [];

        foreach ($entries as $_file => $_size) {
            if (strpos($_file, $prefix) !== 0) {
                continue;
            }

            $instance_count++;
            $instance_size += $_size;

            $_unprefixed = substr($_file, strlen($prefix) + 1);

            $_prefix = substr($_unprefixed, 0, strpos($_unprefixed, "-"));

            if (!isset($shm_entries_by_prefix[$_prefix])) {
                $shm_entries_by_prefix[$_prefix] = [
                    "count" => 0,
                    "size"  => 0,
                ];
            }

            $shm_entries_by_prefix[$_prefix]["count"]++;
            $shm_entries_by_prefix[$_prefix]["size"] += $_size;
        }

        $shm_global_info["instance_size"]  = $instance_size;
        $shm_global_info["instance_count"] = $instance_count;

        $shm_global_info["entries_by_prefix"] = $shm_entries_by_prefix;

        return $shm_global_info;
    }

    public static function getAPCuInfo(?string $version = null): array
    {
        $info = apcu_cache_info(false);

        $engine = 'APCu';

        $hit_rate = 0;
        if (intval($info['num_hits'] + $info['num_misses']) > 0) {
            $hit_rate = round(($info['num_hits'] / ($info['num_misses'] + $info['num_hits'])) * 100, 2);
        }

        $total           = CMbString::fromDecaBinary(ini_get('apc.shm_size'));
        $shm_global_info = [
            '_all_'   => $info,
            'engine'  => $engine,
            'version' => $version,

            'hits'     => $info['num_hits'],
            'misses'   => $info['num_misses'],
            'hit_rate' => $hit_rate,

            'entries'    => $info['num_entries'],
            'expunges'   => isset($info['num_expunges']) ? $info['num_expunges'] : $info['expunges'],
            'start_time' => $info['start_time'],

            'used'       => $info['mem_size'],
            'total'      => $total,
            'total_rate' => 100 * $info['mem_size'] / $total,

            'instance_count' => 0,
            'instance_size'  => 0,
        ];

        $prefix = preg_replace('/[^\w]+/', '_', CAppUI::conf('root_dir'));

        $instance_count = 0;
        $instance_size  = 0;

        $shm_entries_by_prefix = [];

        foreach ($info['cache_list'] as $_file) {
            $_key = $_file['info'];
            if (strpos($_key, $prefix) !== 0) {
                continue;
            }

            $_mem = $_file['mem_size'];
            $instance_count++;
            $instance_size += $_mem;

            $_prefix = substr($_key, strlen($prefix) + 1);

            if (($pos = strpos($_prefix, '-')) !== false) {
                $_prefix = substr($_prefix, 0, $pos);
            }

            if (!isset($shm_entries_by_prefix[$_prefix])) {
                $shm_entries_by_prefix[$_prefix] = [
                    'count' => 0,
                    'size'  => 0,
                ];
            }

            $shm_entries_by_prefix[$_prefix]['count']++;
            $shm_entries_by_prefix[$_prefix]['size'] += $_mem;
        }

        $shm_global_info['instance_size']  = $instance_size;
        $shm_global_info['instance_count'] = $instance_count;

        $shm_global_info['entries_by_prefix'] = $shm_entries_by_prefix;

        return $shm_global_info;
    }

    public static function getRedisInfo(?string $version = null): array
    {
        $conn       = CRedisServer::getClient();
        $info       = $conn->parseMultiLine($conn->getStats());
        $cache_list = $conn->findKeys("*");

        $hit_rate = 0;
        if (intval($info["keyspace_hits"] + $info["keyspace_misses"]) > 0) {
            $hit_rate = round(($info["keyspace_hits"] / ($info["keyspace_misses"] + $info["keyspace_hits"])) * 100, 2);
        }

        $shm_global_info = [
            "_all_"   => $info,
            "engine"  => "Redis",
            "version" => $version,

            "hits"     => $info["keyspace_hits"],
            "misses"   => $info["keyspace_misses"],
            "hit_rate" => $hit_rate,

            "entries"    => count($cache_list),
            "expunges"   => $info["evicted_keys"],
            "start_time" => $info["uptime_in_seconds"],

            "used"       => $info["used_memory"],
            "total"      => null,
            "total_rate" => null,

            "instance_count" => 0,
            "instance_size"  => 0,
        ];

        $prefix = preg_replace('/[^\w]+/', "_", CAppUI::conf("root_dir"));

        $instance_count = 0;
        $instance_size  = 0;

        $shm_entries_by_prefix = [];

        foreach ($cache_list as $_key) {
            if (!$conn->has($_key) || (strpos($_key, $prefix) !== 0)) {
                continue;
            }

            $_mem = strlen($conn->get($_key));
            $instance_count++;
            $instance_size += $_mem;

            $_prefix = substr($_key, strlen($prefix) + 1);
            // Correction pour la vue des entrées de cache
            $_prefix = str_replace('\\', '\\\\', $_prefix);

            if (($pos = strpos($_prefix, '-')) !== false) {
                $_prefix = substr($_prefix, 0, $pos);
            }

            if (!isset($shm_entries_by_prefix[$_prefix])) {
                $shm_entries_by_prefix[$_prefix] = [
                    "count" => 0,
                    "size"  => 0,
                ];
            }

            $shm_entries_by_prefix[$_prefix]["count"]++;
            $shm_entries_by_prefix[$_prefix]["size"] += $_mem;
        }

        $shm_global_info["instance_size"]  = $instance_size;
        $shm_global_info["instance_count"] = $instance_count;

        $shm_global_info["entries_by_prefix"] = $shm_entries_by_prefix;

        return $shm_global_info;
    }

    public static function getAPCuKeysInfo($prefix = null): array
    {
        $info = apcu_cache_info(false);

        $root_prefix = preg_replace('/[^\w]+/', '_', CAppUI::conf('root_dir'));

        $entries = [];

        foreach ($info['cache_list'] as $_cache) {
            $_key = $_cache['info'];
            //$_key    = str_replace('\\', '/', $_cache['info']);
            $_prefix = "$root_prefix-$prefix";

            $split_key = explode('-', $_key);

            // Key not separated by "-", probably because of an other application which also uses APC
            if (!array_key_exists(1, $split_key)) {
                continue;
            }

            $_key_prefix = $split_key[1];

            // Keys of another MB instance
            if ($split_key[0] !== $root_prefix) {
                continue;
            }

            if ($prefix !== $_key_prefix) {
                continue;
            }

            $_subkey = substr($_key, strlen($_prefix) + 1);

            $_entry = [
                'ctime'     => CMbDT::strftime(CMbDT::ISO_DATETIME, $_cache['creation_time']),
                'mtime'     => CMbDT::strftime(CMbDT::ISO_DATETIME, $_cache['mtime']),
                'atime'     => CMbDT::strftime(CMbDT::ISO_DATETIME, $_cache['access_time']),
                'size'      => $_cache['mem_size'],
                'hits'      => $_cache['num_hits'],
                'ttl'       => $_cache['ttl'],
                'ref_count' => $_cache['ref_count'],
                'key'       => substr($_key, strlen($root_prefix) + 1),
            ];

            $entries[$_subkey] = $_entry;
        }

        ksort($entries);

        return $entries;
    }

    public static function getRedisKeysInfo($prefix = null): array
    {
        $entries = [];

        $root_prefix = preg_replace('/[^\w]+/', "_", CAppUI::conf("root_dir"));

        $_client = CRedisServer::getClient();

        $pattern = "-$prefix*";
        $keys    = $_client->findKeys($root_prefix . $pattern);
        $_prefix = $root_prefix . $pattern;

        foreach ($keys as $_key) {
            $_value = $_client->get($_key);

            $_entry = [
                "ctime"     => null,
                "mtime"     => null,
                "atime"     => null,
                "size"      => strlen($_value),
                "hits"      => null,
                "ttl"       => $_client->send("TTL", [$_key]),
                "ref_count" => null,
                "key"       => substr($_key, strlen($root_prefix) + 1),
            ];

            $entries[substr($_key, strlen($_prefix))] = $_entry;
        }

        return $entries;
    }

    public static function getDiskKeysInfo(string $directory, ?string $prefix = null): array
    {
        $entries = [];

        $root_prefix = preg_replace('/[^\w]+/', "_", CAppUI::conf("root_dir"));

        $pattern_glob = self::_path($directory, "$root_prefix-$prefix*");
        $keys         = array_map('basename', glob($pattern_glob));

        foreach ($keys as $_key) {
            $split_key   = explode('-', $_key);
            $_key_prefix = $split_key[1];
            if ($prefix !== $_key_prefix) {
                continue;
            }

            $content = null;
            $path    = self::_path($directory, $_key);
            if (file_exists($path)) {
                // Read header if any
                $fp      = fopen($path, 'rb');
                $content = fread($fp, 16);

                while (!feof($fp)) {
                    $content .= fread($fp, 8192);
                }

                fclose($fp);
            }

            $_entry = [
                "ctime"     => null,
                "mtime"     => null,
                "atime"     => null,
                "size"      => strlen($content),
                "hits"      => null,
                "ttl"       => null,
                "ref_count" => null,
                "key"       => substr($_key, strlen($root_prefix) + 1),
            ];

            $entries[substr($_key, strlen("$root_prefix-$prefix") + 1)] = $_entry;
        }

        ksort($entries);

        return $entries;
    }

    private static function _path(string $directory, $key): string
    {
        return $directory . CMbPath::sanitizeBaseName($key);
    }
}
