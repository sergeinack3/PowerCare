<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Console\Fixtures;

use Ox\Core\CMbArray;
use Ox\Core\Module\CModule;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\GroupFixturesInterface;
use ReflectionClass;
use ReflectionException;

class FixturesFinder
{
    public const GLOB_PATTERN        = '/modules/%s/tests/Fixtures/*';
    public const GLOB_PATTERN_SUFFIX = '*Fixtures.php';

    private string $path = "";

    private array $groups = [];

    private string $namespace = "";

    private array $active_modules = [];

    private static array $fixtures_glob = [];

    /**
     * @param $path
     * @param $groups
     * @param $namespace
     */
    public function __construct(string $path, array $groups = [], string $namespace = "")
    {
        $this->path      = $path;
        $this->groups    = $groups;
        $this->namespace = $namespace;

        // Build an array of mod_names using the module static cache.
        if (!CModule::$active) {
            CModule::loadModules();
        }

        $this->active_modules = CMbArray::pluck(CModule::getActive(), 'mod_name');
    }

    /**
     * Require must be call one time per a hit
     * @return array
     */
    private function findFixtures(): array
    {
        if (empty(static::$fixtures_glob)) {
            $previous_declared_classes = get_declared_classes();
            // Tests classes are not in classmap so glob & include files !
            // Use the active modules names to glob.
            foreach ($this->active_modules as $mod_name) {
                $this->globAndRequireFixtures($this->path . sprintf(static::GLOB_PATTERN, $mod_name));
            }

            // Instance classes
            foreach (array_diff(get_declared_classes(), $previous_declared_classes) as $class) {
                // Ignore parent class
                if ($class === Fixtures::class) {
                    continue;
                }
                static::$fixtures_glob[] = $class;
            }
        }

        return static::$fixtures_glob;
    }

    /**
     * @throws ReflectionException
     */
    public function find(): array
    {
        $fixtures = [];
        // Instance classes
        foreach ($this->findFixtures() as $class) {
            // exclude abstract classes
            $reflection = new ReflectionClass($class);
            if ($reflection->isAbstract()) {
                continue;
            }

            $fixture = new $class();
            // check group
            if ($this->hasGroup()) {
                if (!$fixture instanceof GroupFixturesInterface) {
                    continue;
                }
                $group = $fixture::getGroup()[0];
                if (!$this->matchGroup($group)) {
                    continue;
                }
            }

            // check namespace
            if ($this->hasNameSpace()) {
                if (!$this->matchNameSpace($class)) {
                    continue;
                }
            }

            $fixtures[$class] = $fixture;
        }

        return $fixtures;
    }

    /**
     * @return bool
     */
    private function hasGroup(): bool
    {
        return !empty($this->groups);
    }

    /**
     * @param string $group
     *
     * @return bool
     */
    private function matchGroup(string $group): bool
    {
        return in_array($group, $this->groups);
    }

    /**
     * @return bool
     */
    private function hasNameSpace(): bool
    {
        return !empty($this->namespace);
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    private function matchNameSpace(string $class): bool
    {
        return (
            strpos(stripslashes(strtolower($class)), stripslashes(strtolower($this->namespace))) === 0
        );
    }

    private function setFixturesGlob(array $array): void
    {
        self::$fixtures_glob = $array;
    }

    /**
     * Recursive GLOB from a directory
     *
     * @param string $path
     *
     * @return void
     */
    private function globAndRequireFixtures(string $path): void
    {
        // Find all dirs
        foreach (glob($path, GLOB_ONLYDIR) as $dir) {
            $this->globAndRequireFixtures($dir . DIRECTORY_SEPARATOR . '*');
        }

        // Include all fixtures file
        foreach (glob($path . self::GLOB_PATTERN_SUFFIX) as $file) {
            include_once $file;
        }
    }
}
