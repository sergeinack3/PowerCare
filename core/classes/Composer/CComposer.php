<?php
/**
 * @package Mediboard\Install
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Composer;

/**
 * Interface with composer cli
 */
class CComposer
{
    const BINARY                 = 'composer';
    const URL_PACKAGIST          = 'https://packagist.org/';
    const URL_PACKAGIST_PACKAGES = 'https://packagist.org/packages/';
    const URL_COMPOSER           = 'https://getcomposer.org/';
    const URL_COMPOSER_DOWNLOAD  = 'https://getcomposer.org/download/';
    const COMPOSER_HOME          = '/tmp/composer'; // cache

    private $root_dir;
    private $packages;

    /**
     * CComposer constructor.
     *
     * @param String $root_dir racine mediboard
     */
    public function __construct($root_dir = null)
    {
        if ($root_dir === null) {
            $root_dir = dirname(__DIR__, 3);
        }
        $this->root_dir = $root_dir;
        // necessary for apache access
        putenv('COMPOSER_HOME=' . $this->root_dir . $this::COMPOSER_HOME);
    }


    /**
     *
     * @return bool|string
     */
    public function getVersion()
    {
        $cmd = static::BINARY . ' --version';

        $result = shell_exec($cmd);

        preg_match("/(?:version|v)\s*((?:[0-9]+\.?)+)/i", $result, $matches);

        if (empty($matches)) {
            $retour = false;
        } else {
            $retour = $matches[1];
        }

        return $retour;
    }


    /**
     *
     * @return string|false json
     */
    public function getJson()
    {
        $path = $this->root_dir . DIRECTORY_SEPARATOR . 'composer.json';
        if (file_exists($path)) {
            $retour = file_get_contents($path);
        } else {
            $retour = false;
        }

        return $retour;
    }

    /**
     * @return array
     */
    public function getRequires()
    {
        $json  = $this->getJson();
        $datas = json_decode($json, true);

        $requires     = isset($datas['require']) ? $datas['require'] : [];
        $requires_dev = isset($datas['require-dev']) ? $datas['require-dev'] : [];

        foreach ($requires as $_name => $_version) {
            if (strpos($_name, 'ext-') === 0 || strpos($_name, 'php') === 0) {
                unset($requires[$_name]);
                continue;
            }
            $requires[$_name] = (object)[
                'name'             => $_name,
                'is_dev'           => false,
                'version_required' => $_version,
            ];
        }

        foreach ($requires_dev as $_name => $_version) {
            if (strpos($_name, 'ext-') === 0 || strpos($_name, 'php') === 0) {
                unset($requires_dev[$_name]);
                continue;
            }
            $requires_dev[$_name] = (object)[
                'name'             => $_name,
                'is_dev'           => true,
                'version_required' => $_version,
            ];
        }

        return array_merge($requires, $requires_dev);
    }

    /**
     * @return array
     */
    public function getInstalled()
    {
        $show     = $this->show();
        $packages = json_decode($show);
        $packages = $packages->installed ? $packages->installed : [];

        $licenses = $this->licenses();
        $licenses = json_decode($licenses, true);
        $licenses = $licenses['dependencies'] ? $licenses['dependencies'] : [];

        if (!$this->getVersion()) {
            return [];
        }

        foreach ($packages as $_key => $_package) {
            unset($packages[$_key]);
            $_package->license         = isset($licenses[$_package->name]['license']) ? implode(
                " ",
                $licenses[$_package->name]['license']
            ) : null;
            $packages[$_package->name] = $_package;
        }

        return $packages;
    }

    /**
     * @return array
     */
    public function getPackages()
    {
        if ($this->packages !== null) {
            return $this->packages;
        }

        $packages_installed = $this->getInstalled();
        $packages           = $this->getRequires();

        foreach ($packages as $package_name => $package) {
            if (array_key_exists($package_name, $packages_installed)) {
                $package_installed = $packages_installed[$package_name];

                //dump($package_installed);
                $package->is_installed      = true;
                $package->version_installed = $package_installed->version;
                $package->description       = $package_installed->description;
                $package->license           = $package_installed->license;
            } else {
                $package->is_installed      = false;
                $package->version_installed = null;
                $package->description       = null;
                $package->license           = null;
            }
        }

        return $this->packages = $packages;
    }

    /**
     *
     * @param bool   $direct  root packages
     * @param string $package package name
     *
     * @return string|false json
     */
    public function show($direct = true, $package = null)
    {
        $cmd = static::BINARY . ' show --working-dir=' . escapeshellarg($this->root_dir) . ' --format=json';
        if ($direct) {
            $cmd .= ' --direct';
        }

        if ($package !== null) {
            $pattern = 'show ' . escapeshellarg($package);
            str_replace('show', $pattern, $cmd);
        }

        return shell_exec($cmd);
    }

    public function licenses()
    {
        $cmd    = static::BINARY . ' licenses --working-dir=' . escapeshellarg($this->root_dir) . ' --format=json';
        $result = shell_exec($cmd);

        return $result;
    }


    /**
     *
     * @param string $package
     * @param Bool   $dry_run
     * @param Bool   $output
     *
     * @param bool   $no_dev
     *
     * @return string|false json
     */
    public function install($package = null, $dry_run = false, $output = false, $no_dev = true)
    {
        $cmd = static::BINARY . ' install --working-dir=' . escapeshellarg(
                $this->root_dir
            ) . ' --ignore-platform-reqs -o';

        if ($package !== null) {
            $pattern = 'install ' . escapeshellarg($package);
            str_replace('install', $pattern, $cmd);
        }

        if ($no_dev) {
            $cmd .= ' --no-dev ';
        }

        if ($dry_run) {
            $cmd .= ' --dry-run --no-suggest ';
        }

        if ($output) {
            $cmd .= '  2>&1 ';
        }

        return shell_exec($cmd);
    }

    /**
     * Exec composer install in dry-run mode
     * @return false|int
     */
    public function checkAll()
    {
        $output = $this->install(null, true, true, true);
        // prod & dev time compat (--no-dev)
        if (!(bool)preg_match('/Nothing to install or update/', $output)) {
            return (bool)preg_match('/Package operations: 0 installs, 0 updates/', $output);
        }

        return true;
    }

    /**
     * @return bool|mixed
     */
    public function getPrefixPsr4()
    {
        $file = $this->root_dir . '//vendor//composer//autoload_psr4.php';
        if (!file_exists($file)) {
            return false;
        }

        return include $file;
    }

    /**
     * @return int
     */
    public function countPackages()
    {
        return count($this->getPackages());
    }

    /**
     * @return int
     */
    public function countPackagesInstalled()
    {
        $packages = $this->getPackages();
        $count    = 0;
        foreach ($packages as $package) {
            $count = $package->is_installed ? $count + 1 : $count;
        }

        return $count;
    }

    /**
     * @param mixed $root_package Composer\Package\Package
     *
     * @return string $msg
     */
    public function addPrefixPsr4FromModulesComposer($root_package)
    {
        $time_start   = microtime(true);
        $autoload     = $root_package->getAutoload();
        $pattern      = $this->root_dir . '/modules/*/composer.json';
        $prefix_count = 0;

        foreach (glob($pattern) as $_filename) {
            $content = file_get_contents($_filename);
            $datas   = json_decode($content, true);

            if (!isset($datas['autoload']['psr-4'])) {
                continue;
            }

            $rules = $datas['autoload']['psr-4'];
            preg_match('/modules(\/|\\\)(?P<mod_name>.+)(\/|\\\)composer.json$/', $_filename, $matches);
            $_module = isset($matches['mod_name']) ? $matches['mod_name'] : null;

            foreach ($rules as $_prefix => $_folder) {
                $autoload['psr-4'][$_prefix] = "modules/{$_module}/{$_folder}";
                $prefix_count++;
            }
        }

        $root_package->setAutoload($autoload);

        $time = round(microtime(true) - $time_start, 3);

        return "Merge {$prefix_count} psr-4 prefix from modules/*/composer.json during {$time} sec";
    }

}
