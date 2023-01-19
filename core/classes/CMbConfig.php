<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;
use Ox\Core\Config\Config;

/**
 * Class CMbConfig
 *
 * Mediboard configuration accessor and mutator
 */
class CMbConfig
{
    const CONFIG_FILE          = 'includes/config.php';
    const CONFIG_DIST_FILE     = 'includes/config_dist.php';
    const CONFIG_OVERLOAD_FILE = 'includes/config_overload.php';

    /** @var array Parser options */
    public $options = [
        'name' => 'dPconfig',
    ];

    /** @var array Configuration values */
    public $values = [];

    /** @var string Default configuration path */
    public $sourcePath = '';

    /** @var string Main configuration path */
    public $targetPath = '';

    /** @var string Overload configuration path */
    public $overloadPath = '';

    // Ignorer également db et php
    /** @var array Forbidden configurations to store in DB */
    static $forbidden_values = [
        'config_db',
        'root_dir',
        'instance_role',
        'base_url',
        'servers_ip',
        'offline_time_start',
        'offline_time_end',
    ];

    /**
     * CMbConfig constructor.
     *
     * @param null $path
     */
    function __construct($path = null)
    {
        if ($path !== null) {
            // without global
            $path               = substr($path, -1) !== '/' ? $path . '/' : $path;
            $this->sourcePath   = $path . static::CONFIG_DIST_FILE;
            $this->targetPath   = $path . static::CONFIG_FILE;
            $this->overloadPath = $path . static::CONFIG_OVERLOAD_FILE;
        } else {
            // legacy
            global $mbpath;
            $this->sourcePath   = $mbpath . "includes/config_dist.php";
            $this->targetPath   = $mbpath . "includes/config.php";
            $this->overloadPath = $mbpath . "includes/config_overload.php";
        }
    }

    /**
     * @return bool
     */
    public function isConfigFileExists(): bool
    {
        return file_exists($this->targetPath);
    }

    /**
     * Guess config values
     *
     * @return void
     */
    function guessValues()
    {
        global $mbpath;

        $this->values['root_dir'] = strtr(realpath($mbpath), "\\", "/");
        $this->values['base_url'] = "http://" . $_SERVER["HTTP_HOST"] . dirname(dirname($_SERVER["PHP_SELF"]));
    }

    /**
     * Load config values from path
     *
     * @param string $path Configuration path
     *
     * @return array
     * @throws Exception
     */
    function loadValuesFromPath($path)
    {
        if (!is_file($path ?? '')) {
            return [];
        }

        $config = new Config();

        try {
            $configContainer = $config->parseConfig($path, $this->options);
        } catch (Exception $e) {
            return [];
        }

        $rootConfig = $configContainer->toArray();

        return $rootConfig['root'];
    }

    /**
     * Load all config values
     * @return void
     * @throws Exception
     */
    function load()
    {
        $this->values = [];
        $this->values = array_replace_recursive(
            $this->values,
            $this->loadValuesFromPath($this->sourcePath),
            $this->loadValuesFromPath($this->targetPath),
            $this->loadValuesFromPath($this->overloadPath)
        );

        if (!is_file($this->targetPath)) {
            $this->guessValues();
        }
    }

    /**
     * Update config values
     *
     * @param array $newValues New values
     * @param bool  $keepOld   Whether to keep old value or not
     *
     * @return bool|null
     * @throws Exception
     */
    public function update(array $newValues = [], bool $keepOld = true): ?bool
    {
        // Avoid passing null to stripslashes which would result on a deprecation notice
        array_walk_recursive( $newValues, function ($value) {
            return $value !== null ? stripslashes($value) : '';
        });

        if ($keepOld) {
            global $dPconfig;

            $conf = $dPconfig;

            $newValues = CMbArray::mergeRecursive($conf, $newValues);
        }

        if (!count($newValues)) {
            if (is_file($this->targetPath)) {
                unlink($this->targetPath);
            }

            return null;
        }

        $this->values = $newValues;
        $dPconfig     = $this->values;

        $config = new Config();
        $config->parseConfig($this->values, $this->options);

        return $config->writeConfig($this->targetPath, $this->options);
    }

    /**
     * Set a configuration
     *
     * @param string $path  Path
     * @param mixed  $value Value
     *
     * @return void
     */
    function set($path, $value)
    {
        $conf   = $this->values;
        $values = &$conf;

        $items = explode(' ', $path);
        foreach ($items as $part) {
            if (!array_key_exists($part, $conf)) {
                $conf[$part] = [];
            }

            $conf = &$conf[$part];
        }
        $conf = $value;

        $this->values = $values;
    }

    /**
     * Get a configuration from its path
     *
     * @param string $path Configuration path
     *
     * @return mixed
     */
    function get($path)
    {
        $conf = $this->values;

        $items = explode(' ', $path);
        foreach ($items as $part) {
            if (!isset($conf[$part])) {
                return false;
            }
            $conf = $conf[$part];
        }

        return $conf;
    }

    /**
     * Load a configuration
     *
     * @param string $key    Key
     * @param string $value  Value
     * @param array  $config Configuration array
     *
     * @return void
     */
    static function loadConf($key, $value, &$config)
    {
        if (count($key) > 1) {
            $firstkey = array_shift($key);
            if (!isset($config[$firstkey])) {
                $config[$firstkey] = [];
            }
            self::loadConf($key, $value, $config[$firstkey]);
        } else {
            $config[$key[0]] = $value;
        }
    }

    /**
     * Build configuration list from config array
     *
     * @param array  $list  Config list
     * @param array  $array Configuration array
     * @param string $_key  Key
     *
     * @return void
     */
    static function buildConf(&$list, $array, $_key)
    {
        foreach ($array as $key => $value) {
            $_conf_key = ($_key ? "$_key " : "") . $key;
            if (is_array($value)) {
                self::buildConf($list, $value, $_conf_key);
                continue;
            }
            $list[$_conf_key] = $value;
        }
    }

    /**
     * Load config values from database
     *
     * @return void
     */
    static function loadValuesFromDB()
    {
        global $dPconfig;
        $ds = CSQLDataSource::get("std");

        // This will fail if `config_db` table does not exist
        $request = "SELECT * FROM config_db WHERE config_db.key " . CSQLDataSource::prepareNotIn(
                self::$forbidden_values
            );
        $configs = $ds->loadList($request);
        if (!empty($configs)) {
            foreach ($configs as $_value) {
                CMbConfig::loadConf(explode(" ", $_value['key']), $_value['value'], $dPconfig);
            }
        }

        // Réinclusion du config_overload
        if (is_file(__DIR__ . "/../../includes/config_overload.php")) {
            include __DIR__ . "/../../includes/config_overload.php";
        }
    }

    /**
     * Set a configuration in DB
     *
     * @param string $key   Path of the configuration
     * @param string $value Value of the configuration
     */
    static function setConfInDB($key, $value)
    {
        $ds    = CSQLDataSource::get("std");
        $query = "INSERT INTO `config_db`
                VALUES (%1, %2)
                ON DUPLICATE KEY UPDATE `value` = %2";

        $ds->exec($ds->prepare($query, $key, $value));
    }
}
