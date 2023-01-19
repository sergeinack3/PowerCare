<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Kernel\Services;

use Symfony\Component\Yaml\Yaml;

/**
 * Aggregate all necessary services to all_services.yml
 */
class ServicesManager
{
    public const FILE_PATH = 'includes/all_services.yml';

    public const SERVICES_DIRNAME = 'config/services';

    public const IMPORTS_ROOT_NODE     = 'imports';
    public const SERVICES_ROOT_NODE    = 'services';
    public const DEFAULT_NODE          = '_defaults';
    public const DEFAULT_AUTOWIRE      = 'autowire';
    public const DEFAULT_AUTOCONFIGURE = 'autoconfigure';
    public const NODE_RESOURCE         = 'resource';
    public const NODE_EXCLUDE          = 'exclude';

    public const COMPOSER_AUTOLOAD = 'autoload';
    public const COMPOSER_PSR      = 'psr-4';

    public const DIR       = 'dir';
    public const NAMESPACE = 'ns';

    private $root_dir;

    private $file_path;

    private $controllers_dirs = [];

    private $services_dirs = [];

    private $content = [];

    public function __construct()
    {
        $this->root_dir  = dirname(__DIR__, 4);
        $this->file_path = $this->root_dir . DIRECTORY_SEPARATOR . self::FILE_PATH;
    }

    public function buildAllServices(): string
    {
        $time_start = microtime(true);

        $this->removeOldFile();

        $this->getAllDirs();

        $this->buildDefaultContent();
        $this->addImports();
        $this->addControllers();

        $this->writeFile();

        // Exclude _defaults
        $count_services = count($this->content[self::SERVICES_ROOT_NODE]) - 1;
        $time           = round(microtime(true) - $time_start, 3);

        return "Generated services file in {$this->file_path} containing {$count_services} services during {$time} sec";
    }

    protected function removeOldFile(): void
    {
        // Remove existing file
        if (file_exists($this->file_path) && is_file($this->file_path)) {
            unlink($this->file_path);
        }
    }

    protected function writeFile(): void
    {
        // Use 4 depth to never switch to inline
        file_put_contents($this->file_path, (new Yaml())->dump($this->content, 4));
    }

    private function addImports(): void
    {
        $imports = [];
        foreach ($this->services_dirs as $service) {
            $imports[] = [self::NODE_RESOURCE => "..{$service}"];
        }

        if (!empty($imports)) {
            $this->content[self::IMPORTS_ROOT_NODE] = $imports;
        }
    }

    private function addControllers(): void
    {
        foreach ($this->controllers_dirs as [self::DIR => $dir, self::NAMESPACE => $ns]) {
            $this->content[self::SERVICES_ROOT_NODE][$ns] = [
                self::NODE_RESOURCE => "..{$dir}",
                self::NODE_EXCLUDE  => [
                    "..{$dir}/Legacy",
                ],
            ];
        }
    }

    private function getAllDirs(): void
    {
        $modules_dir = $this->root_dir . DIRECTORY_SEPARATOR . 'modules';

        foreach (glob($modules_dir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $dirname) {
            $this->addControllersDir($dirname);
            $this->addServicesDir($dirname);
        }
    }

    private function addControllersDir(string $dirname): void
    {
        $json_path       = $dirname . DIRECTORY_SEPARATOR . 'composer.json';
        $controllers_dir = $dirname . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Controllers';
        if (!file_exists($json_path) || !is_dir($controllers_dir)) {
            return;
        }

        $json_data = json_decode(file_get_contents($json_path), true);

        $this->controllers_dirs[] = [
            self::DIR       => str_replace($this->root_dir, '', $controllers_dir),
            self::NAMESPACE => $this->getNamespace($json_data),
        ];
    }

    private function addServicesDir(string $dirname): void
    {
        $service_dir = $dirname . DIRECTORY_SEPARATOR . self::SERVICES_DIRNAME;
        if (!is_dir($service_dir)) {
            return;
        }

        $this->services_dirs[] = str_replace($this->root_dir, '', $service_dir) . DIRECTORY_SEPARATOR;
    }

    private function getNamespace(array $data): ?string
    {
        if (!isset($data[self::COMPOSER_AUTOLOAD][self::COMPOSER_PSR])) {
            return null;
        }

        foreach (array_keys($data[self::COMPOSER_AUTOLOAD][self::COMPOSER_PSR]) as $namespace) {
            // Do not take the Tests NS
            if (!str_ends_with($namespace, '\\Tests\\')) {
                return $namespace . 'Controllers\\';
            }
        }

        return null;
    }

    private function buildDefaultContent(): void
    {
        $this->content = [
            self::IMPORTS_ROOT_NODE  => [],
            self::SERVICES_ROOT_NODE => [
                self::DEFAULT_NODE => [
                    self::DEFAULT_AUTOWIRE      => true,
                    self::DEFAULT_AUTOCONFIGURE => true,
                ],
            ],
        ];
    }
}
