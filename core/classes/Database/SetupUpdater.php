<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Database;

use Composer\Semver\Comparator as SemverComparator;
use ErrorException;
use Exception;
use Ox\Core\CApp;
use Ox\Core\CSetup;
use Ox\Core\Module\CModule;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;
use Throwable;

class SetupUpdater
{
    protected const CORE_TYPE  = 'core';
    protected const EXCLUSIONS = [];

    /** @var SymfonyStyle */
    protected $io;

    /** @var Stopwatch */
    private $stopwatch;

    /** @var CSetup[] */
    private $setups = [];

    /** @var CModule[] */
    private $modules = [];

    /** @var string[]  */
    private $core = [];

    /** @var array */
    private $dependencies = [];

    /** @var array */
    private $steps = [];

    /** @var array  */
    protected static $errors = [];

    /** @var string|null */
    private static $current = null;

    /** @var bool */
    private $install = false;

    /** @var bool */
    private $dryrun = false;

    /** @var array  */
    private $durations = [];

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->stopwatch = new Stopwatch();
    }

    /**
     * Enables command line mode (sets custom error handling)
     *
     * @param SymfonyStyle $io
     * @return $this
     */
    public function enableCommandLineMode(SymfonyStyle $io): self
    {
        $this->io = $io;
        $this->setCommandLineErrorHandling();

        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setInstallMode(bool $value = false): self
    {
        $this->install = $value;

        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setDryRunMode(bool $value = false): self
    {
        $this->dryrun = $value;

        return $this;
    }

    /**
     * Runs the update of included modules, by resolving dependencies and making update steps
     *
     * @throws Exception
     */
    public function run(?string $name = null, ?array $include = [], ?array $exclude = []): void
    {
        $this->prepare();

        $name    = !empty($name) ? $name : 'all';
        $include = !empty($include) ? $include : array_keys($this->modules);

        $this->stopwatch->start($name);

        if ($this->canDisplay()) {
            $this->io->section('Running sequence named : ' . $name);
        }

        shuffle($include);

        foreach ($include as $module) {
            $this->resolve($module);
        }

        $this->sanitize();
        $this->exclude($exclude);

        $this->execute();

        $this->displayDurations();

        if ($this->canDisplay()) {
            $this->io->writeln('Performed in ' . $this->stopwatch->stop($name)->getDuration() . 'ms.');
        }
    }

    /**
     * Performs a default sequence where core modules are updated beforehand and then the rest of the modules
     *
     * @throws Exception
     */
    public function runAll(): self
    {
        $this->initModules();

        $this->run(self::CORE_TYPE, $this->core);
        $this->run(
            'all',
            null,
            array_merge(
                $this->core,
                self::EXCLUSIONS
            )
        );

        return $this;
    }

    /**
     * @throws Exception
     */
    private function prepare(): void
    {
        if (empty($this->modules) && empty($this->setups)) {
            $this->initModules();
        }
        if (empty($this->dependencies)) {
            $this->initDependencies();
        }

        $this->steps     = [];
        $this->durations = [];
    }

    /**
     * Performs the module upgrades along the defined steps
     *
     * @throws Exception
     */
    private function execute(): void
    {
        static::$errors = [];

        if ($this->canDisplay() && empty($this->steps)) {
            $this->io->note('No upgrade needed for the current sequence');
            return;
        }

        $steps = count($this->steps);

        if ($this->canDisplay()) {
            $this->io->progressStart($steps);
        }

        foreach ($this->steps as $step => $item) {
            $name     = $item['module'];
            $revision = $item['revision'];

            static::$current = $name;

            $module = $this->modules[$name];
            $setup =  $this->setups[$name];

            if (null === $module->_id) {
                $module->mod_version = "0.0";
                $module->mod_active    = 1;
                $module->mod_ui_active = 1;
                $module->store();

                $this->modules[$name]      = $module;
                CModule::$installed[$name] = $module;

                CModule::loadModules();
            }

            if ($this->canDisplay() && $this->io->isVeryVerbose()) {
                $this->io->writeln(
                    "(" . ($step + 1) . "/" . $steps . ") : " . $name
                    . '(' . $module->mod_version . '->' . $revision . ')'
                );
            }

            $initialRevision = $module->mod_version;

            $eventName = $step . $name;
            $this->stopwatch->start($eventName);

            if (!$this->dryrun) {
                $setup->upgrade(
                    $module,
                    false,
                    $revision
                );

                $this->reportDuration(
                    $name,
                    $this->stopwatch->stop($eventName)->getDuration()
                );
            }

            $this->reloadModule($name, $initialRevision, $setup);

            if ($this->canDisplay()) {
                $this->io->progressAdvance();
            }
        }

        if ($this->canDisplay()) {
            $this->io->progressFinish();
        }

        if ($this->canDisplay() && count(static::$errors)) {
            if ($this->io->isVerbose()) {
                $this->displayErrors();
            }
            $this->io->caution(count(static::$errors) . " errors were raised during sequence !");
        }
    }

    /**
     * Initializes modules from existing setup classes in source code
     *
     * @throws Exception
     */
    private function initModules(): void
    {
        /* Find All Setup Classes */
        $setupClasses = CApp::getChildClasses(CSetup::class);

        foreach ($setupClasses as $setupClass) {
            /** @var CSetup $setup */
            $setup = new $setupClass();
            $module = new CModule();
            $module->compareToSetup($setup);
            if ($module->mod_type === self::CORE_TYPE) {
                $this->core[] = $module->mod_name;
            }

            $this->modules[$module->mod_name] = $module;
            $this->setups[$module->mod_name] = $setup;
        }

        CModule::loadModules();
    }

    /**
     * Builds an array representing modules dependencies
     *
     * @return void
     */
    private function initDependencies(): void
    {
        $result = [];

        foreach ($this->modules as $module) {
            foreach ($module->_dependencies as $revision => $dependencies) {
                if (!empty($dependencies)) {
                    if (!array_key_exists($module->mod_name, $result)) {
                        $result[$module->mod_name] = [];
                    }

                    foreach ($dependencies as $dependency) {
                        $result[$module->mod_name][$revision][$dependency->module] = $dependency->revision;
                    }
                }

                if (empty($result[$module->mod_name])) {
                    unset($result[$module->mod_name]);
                }
            }
        }

        $this->dependencies = $result;
    }

    /**
     * Update module and setup if it has been upgraded from revision
     *
     * @param string $name
     * @param string $revision
     * @param CSetup $setup
     * @return void
     */
    private function reloadModule(string $name, string $revision, CSetup $setup): void
    {
        $this->setups[$name] = $setup;

        $module = new CModule();
        $module->compareToSetup($setup);

        if (SemverComparator::greaterThan($module->mod_version, $revision)) {
            $this->modules[$name] = $module;
            CModule::$installed[$name] = $module;
        }
    }

    /**
     * Removes useless steps (already updated previously or not to be installed)
     *
     * @return void
     */
    private function sanitize(): void
    {
        if (empty($this->steps)) {
            return;
        }

        $names = array_unique(array_column($this->steps, 'module'));

        foreach ($names as $name) {
            $latest = null;
            foreach ($this->steps as $position => $sequence) {
                /* Don't install module unless install mode is on */
                if (false === $this->install && !$this->modules[$sequence['module']]->mod_id) {
                    unset($this->steps[$position]);
                    continue;
                }

                /* Remove steps if module is already at the latest version */
                if (!$this->modules[$sequence['module']]->_upgradable) {
                    unset($this->steps[$position]);
                    continue;
                }

                if ($name === $sequence['module']) {
                    if (null === $latest || SemverComparator::greaterThan($sequence['revision'], $latest)) {
                        $latest = $sequence['revision'];
                    } else {
                        unset($this->steps[$position]);
                    }
                }
            }
        }

        /* Reset keys after unsets */
        $this->steps = array_values($this->steps);
    }

    /**
     * Removes upgrades steps from the array if excluded targets were provided
     *
     * @param array $modules
     *
     * @return void
     */
    private function exclude(array $modules): void
    {
        if (empty($modules)) {
            return;
        }

        foreach ($modules as $name) {
            foreach ($this->steps as $position => $sequence) {
                if ($name === $sequence['module']) {
                    unset($this->steps[$position]);
                }
            }
        }

        /* Reset keys after unset */
        $this->steps = array_values($this->steps);
    }

    /**
     * Builds an array of upgrade steps by resolving a module dependencies recursively
     * @param string      $module
     * @param string|null $revision
     * @param int         $level
     *
     * @return void
     */
    private function resolve(string $module, string $revision = null, int $level = 0): void
    {
        if (null === $revision) {
            $revision = $this->setups[$module]->mod_version;
        }

        $dependencies = $this->dependencies[$module] ?? null;

        if (!empty($dependencies)) {
            foreach ($dependencies as $dependencyRevision => $dependency) {
                /* Ensure the previous module revision is executed before looking for its dependencies */
                if ($level === 0) {
                    $revisions = $this->setups[$module]->revisions;
                    $key = array_search($dependencyRevision, $revisions);

                    if ($key > 0) {
                        $this->steps[] = [
                            'module'   => $module,
                            'revision' => $revisions[$key - 1],
                            'level'    => $level,
                        ];
                    }
                }

                if (SemverComparator::greaterThan($revision, $dependencyRevision)) {
                    foreach ($dependency as $targetModule => $targetRevision) {
                        $this->resolve($targetModule, $targetRevision, $level + 1);
                    }
                }
            }
        }

        $this->steps[] = [
            'module'   => $module,
            'revision' => $revision,
            'level'    => $level,
        ];
    }

    /**
     * @param string $name
     * @param int $duration
     * @return void
     */
    private function reportDuration(string $name, int $duration): void
    {
        if (array_key_exists($name, $this->durations)) {
            $this->durations[$name] += $duration;
        } else {
            $this->durations[$name] = $duration;
        }
    }

    /**
     * Return an array of differences if any, between current and expected module versions
     *
     * @return array
     */
    public function getStatus(): array
    {
        $failures = [];
        foreach ($this->modules as $name => $module) {
            /* Exclude globally excluded modules */
            if (in_array($name, self::EXCLUSIONS)) {
                continue;
            }

            /* Exclude non-installed modules status unless install mode is on */
            if (!$this->install && !array_key_exists($name, CModule::getInstalled())) {
                continue;
            }

            $setup = $this->setups[$name];
            if (SemverComparator::lessThan($module->mod_version, $setup->mod_version)) {
                $failures[] = [
                    "name"     => $module->mod_name,
                    "current"  => $module->mod_version,
                    "expected" => $setup->mod_version,
                ];
            }
        }

        return $failures;
    }

    /**
     * @return array
     */
    public function getDurations(): array
    {
        return $this->durations;
    }

    /**
     * Returns modules array (only installed modules if install mode is off)
     *
     * @return CModule[]
     */
    public function getModules(): array
    {
        return $this->install ? $this->modules : CModule::getInstalled();
    }

    /**
     * @param int|null $limit
     * @return void
     */
    public function displayDurations(?int $limit = 10): void
    {
        $durations = $this->getDurations();

        if ($this->canDisplay() && $this->io->isVerbose() && count($durations) && !$this->dryrun) {
            arsort($durations);
            $table = $this->io->createTable()
                ->setHeaderTitle('Durations')
                ->setHeaders(['name', 'duration (ms)']);

            if (!empty($limit) && $limit <= $durations) {
                $table->setFooterTitle('Max results: ' . $limit);
            }

            $counter = 0;
            foreach ($durations as $name => $duration) {
                $table->addRow([$name, $duration]);
                $counter++;
                if (!empty($limit) && $counter >= $limit) {
                    break;
                }
            }
            $table->render();
        }
    }

    /*****************************************************
     * CUSTOM ERROR MANAGEMENT (USING COMMAND LINE ONLY) *
     ****************************************************/

    /**
     * @param string $code
     * @param string $text
     * @param string $file
     * @param string $line
     * @return void
     */
    public function errorHandler(string $code, string $text, string $file, string $line): void
    {
        $error_reporting = error_reporting();
        if ($error_reporting || strpos($text, "FATAL ERROR") === 0) {
            $e = new ErrorException($text, $code, 1, $file, $line);
            $this->exceptionHandler($e);
        }
    }

    /**
     * @todo: Output can be messed up with recurrent errors, get rid of the ignored if possible
     *
     * @param Throwable $e
     * @return void
     */
    public function exceptionHandler(Throwable $e): void
    {
        $errorData = [
            'module'   => static::$current ?? 'N/A',
            'revision' => $this->setups[static::$current] instanceof CSetup ? $this->setups[static::$current]::$current ?? 'N/A' : 'N/A',
            'location' => 'File: ' . $e->getFile() . ' l.' . $e->getLine(),
            'message'  => $e->getMessage(),
        ];

        static::$errors[] = $errorData;

        /* Display error at runtime with trace if in debug */
        if ($this->canDisplay() && $this->io->isDebug()) {
            $errorData['trace'] = $e->getTraceAsString() ?? 'N/A';
            $this->io->error($errorData);
        }
    }

    /**
     * @return void
     */
    public function displayErrors(): void
    {
        if (count(static::$errors)) {
            $this->io->createTable()
                ->setHeaderTitle('Errors')
                ->setHeaders(['module', 'revision', 'location', 'message'])
                ->setRows(static::$errors)
                ->render()
            ;
        }
    }

    /**
     * Enables custom error handling using command line
     *
     * @return void
     */
    private function setCommandLineErrorHandling(): void
    {
        error_reporting(E_WARNING);
        set_error_handler([$this, 'errorHandler']);
        set_exception_handler([$this, 'exceptionHandler']);
    }

    /**
     * @return bool
     */
    private function canDisplay(): bool
    {
        return $this->io instanceof SymfonyStyle && !$this->dryrun;
    }
}
