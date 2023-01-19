<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Console\Fixtures;

use Exception;
use Ox\Cli\Console\IAppDependantCommand;
use Ox\Cli\Console\OutputStyleStepTrait;
use Ox\Cli\MediboardCommand;
use Ox\Core\CMbArray;
use Ox\Core\CSQLDataSource;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Files\CFile;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesSkippedException;
use Ox\Tests\Fixtures\GroupFixturesInterface;
use ReflectionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FixturesLoader extends MediboardCommand implements IAppDependantCommand
{
    use FixturesTrait;
    use OutputStyleStepTrait;

    private array $groups           = [];
    private array $fixtures         = [];
    private array $fixtures_ordered = [];

    // Options
    private string $namespace = "";

    private bool $is_append;
    private bool $dry_run;
    private bool $only_purge;
    private bool $full_mode;

    // Stats/reports
    private array $stats = [
        "time"       => [
            "purge" => 0,
            "load"  => 0,
        ],
        "references" => [
            "purge" => 0,
            "load"  => 0,
        ],
        "state"      => [
            "failed"  => [],
            "skipped" => 0,
            "total"   => 0,
        ],
        "queries"    => [
            "purge" => [
                's' => 0,
                'i' => 0,
                'u' => 0,
                'd' => 0,
            ],
            "load"  => [
                's' => 0,
                'i' => 0,
                'u' => 0,
                'd' => 0,
            ],
        ],
    ];

    /**
     * @return void
     * @see parent::configure()
     */
    protected function configure(): void
    {
        $this
            ->setName('ox-fixtures:load')
            ->setDescription('Load Fixtures')
            ->addOption(
                'namespace',
                null,
                InputOption::VALUE_REQUIRED,
                'If you only want to execute fixtures of a specific namespace (WILDCARD)',
            )
            ->addOption(
                'groups',
                'g',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'If you only want to execute some of your fixtures classes',
                []
            )->addOption(
                'append',
                'a',
                InputOption::VALUE_NONE,
                'If you do not want the load command purges the database',
            )->addOption(
                'only-purge',
                'p',
                InputOption::VALUE_NONE,
                'If you only want the load to purge fixtures',
            )->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Dry run mode',
            )->addOption(
                'full',
                null,
                InputOption::VALUE_NONE,
                'Full mode may load more references',
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->input  = $input;
        $this->io     = new SymfonyStyle($this->input, $this->output);
        $this->path   = dirname(__DIR__, 4);

        $this->namespace  = (string)$input->getOption('namespace');
        $this->groups     = (array)$input->getOption('groups');
        $this->dry_run    = (bool)$input->getOption('dry-run');
        $this->is_append  = $this->dry_run || $input->getOption('append');
        $this->only_purge = (bool)$input->getOption('only-purge');
        $this->full_mode  = (bool)$input->getOption('full');

        if (!CModule::getActive('sourceCode')) {
            $this->io->error('Missing module sourceCode to purge/load fixtures');
            return self::FAILURE;
        }

        $this->confirm()
            ->bootstrap()
            ->findFixtures()
            ->orderFixtures()
            ->loadFixtures();

        if ($this->output->isVeryVerbose()) {
            Fixtures::dumpLogs();
        }

        // Output total execution time and Purge & Load details in verbose mode
        if (!$this->dry_run && $this->output->isVerbose()) {
            $this->getAllStatsAsTable();
        }

        return self::SUCCESS;
    }

    /**
     * @return void
     */
    private function loadFixtures(): void
    {
        $this->stats['state']['total'] = count($this->fixtures);

        /* PURGE */
        if (!$this->is_append) {
            $type = 'purge';

            $this->output->writeln('<comment>Purge ' . $this->stats['state']['total'] . ' fixtures :</comment>');

            // Reverse fixtures to purge dependencies at the end
            $fixtures = array_reverse($this->fixtures);

            //Init output style
            $this->initStep($this->output, $this->stats['state']['total'], 50);

            /** @var Fixtures $fixture */
            foreach ($fixtures as $fixture) {
                $class_name                  = get_class($fixture);
                CSQLDataSource::$log_entries = [];
                $time_start                  = microtime(true);

                if ($this->output->isVerbose()) {
                    $this->output->write("- {$class_name}");
                }

                // Execute and catch exception
                if (!$this->displayStep($fixture, $type)) {
                    continue;
                }

                $time    = round(microtime(true) - $time_start, 3);
                $refs    = $fixture->countLogsDelete();
                $queries = $this->getQueriesStats(CSQLDataSource::$log_entries);

                // Stats
                $this->setStats($type, $time, $refs, $queries);

                if ($this->output->isVerbose()) {
                    $this->output->writeln(" > {$refs} refs in {$time} sec {$queries}");
                }
            }

            $this->io->newLine();

            if (!$this->output->isVerbose()) {
                $this->displayResume($type);
            }

            $this->io->newLine();
        }

        /* LOAD */
        if (!$this->only_purge) {
            $type = 'load';

            //Init output style
            $this->initStep($this->output, $this->stats['state']['total'], 50);

            $this->output->writeln('<info>Load ' . $this->stats['state']['total'] . ' fixtures :</info>');

            if ($this->dry_run) {
                $this->output->writeln('<comment>DRY RUN MODE</comment>');
            }

            /** @var Fixtures $fixture */
            foreach ($this->fixtures as $fixture) {
                $class_name                  = get_class($fixture);
                CSQLDataSource::$log_entries = [];

                $fixture->setFullMode($this->full_mode);
                $time_start = microtime(true);

                if ($this->output->isVerbose() || $this->dry_run) {
                    $this->output->write("- {$class_name}");
                }

                // If dry-run mode, only display class name and don't continue
                if ($this->dry_run) {
                    $this->io->newLine();
                    continue;
                }

                // Execute and catch exception
                if (!$this->displayStep($fixture, $type)) {
                    continue;
                }

                $time    = round(microtime(true) - $time_start, 3);
                $refs    = $fixture->countLogsStore();
                $queries = $this->getQueriesStats(CSQLDataSource::$log_entries);

                // Stats
                $this->setStats($type, $time, $refs, $queries);

                if ($this->output->isVerbose()) {
                    $this->output->writeln(" > {$refs} refs in {$time} sec {$queries}");
                }
            }

            $this->io->newLine();

            if (!$this->output->isVerbose() && !$this->dry_run) {
                $this->displayResume($type);
            }

            $this->io->newLine();
        }
    }

    private function displayStep(Fixtures $fixture, string $type): bool
    {
        $failed_type = $this->execStep($fixture, $type);

        if ($failed_type === 'continue') {
            return false;
        }

        // Display dotted output in non verbose mode
        if (!$this->output->isVerbose()) {
            if ($failed_type) {
                $this->step($failed_type);
            } else {
                $this->step('.');
            }
        }

        return true;
    }

    private function execStep(Fixtures $fixture, string $type): string
    {
        $failed_type = '';
        try {
            $fixture->{$type}();
        } catch (FixturesSkippedException $s) {
            $failed_type = 'S';
            $this->stats['state']['skipped']++;

            // Display skipped message in verbose mode
            if ($this->output->isVerbose()) {
                $msg = $s->getMessage() !== '' ? $s->getMessage() : 'without message';
                $this->output->writeln("> Skipped ({$msg})");
            }
        } catch (Exception $e) {
            $failed_type                      = 'F';
            $this->stats['state']['failed'][] = [
                "class"     => get_class($fixture),
                "exception" => $e,
            ];

            if ($this->output->isVerbose()) {
                $this->output->write("> {$e->getTraceAsString()}");
            }
        }

        if ($this->output->isVerbose() && $failed_type !== '') {
            $failed_type = "continue";
        }

        return $failed_type;
    }

    private function displayResume(string $type): void
    {
        $this->output->writeln(
            sprintf(
                "%s %d fixtures (%d skipped, %d failed) in Time: %.2f sec, Refs: %d, Queries: %s",
                ucfirst($type),
                $this->stats['state']['total'],
                $this->stats['state']['skipped'],
                count($this->stats['state']['failed']),
                $this->stats['time'][$type],
                $this->stats['references'][$type],
                $this->getQueriesStatsAsString($type)
            )
        );

        // Display all failure
        if (($count_failed = count($this->stats['state']['failed'])) > 0) {
            $this->io->newLine();
            $this->output->writeln(
                sprintf(
                    "There was %d failure:",
                    $count_failed
                )
            );

            foreach ($this->stats['state']['failed'] as $key => $failed) {
                $this->output->writeln(sprintf("%d) %s", ($key + 1), $failed['class']));
                $this->output->writeln($failed['exception']->getMessage());
                $this->output->writeln("Stack trace:");
                $this->output->writeln($failed['exception']->getTraceAsString());
                $this->io->newLine();
            }
        }
    }

    private function getQueriesStats(array $queries): string
    {
        $s = $i = $u = $d = 0;
        foreach ($queries as $query) {
            $query = str_replace('"', '', $query[0]);
            if (str_starts_with($query, 'SELECT')) {
                $s++;
            } elseif (str_starts_with($query, 'INSERT')) {
                $i++;
            } elseif (str_starts_with($query, 'UPDATE')) {
                $u++;
            } elseif (str_starts_with($query, 'DELETE')) {
                $d++;
            }
        }

        return '(s' . $s . ' i' . $i . ' u' . $u . ' d' . $d . ')';
    }

    private function getQueriesStatsAsArray(string $queries): array
    {
        $query = explode(' ', str_replace(["(", ")", "s", "i", "u", "d"], '', $queries));

        return [
            's' => $query[0],
            'i' => $query[1],
            'u' => $query[2],
            'd' => $query[3],
        ];
    }

    private function getQueriesStatsAsString(string $type): string
    {
        return 's' . $this->stats['queries'][$type]['s'] . ' i' . $this->stats['queries'][$type]['i']
            . ' u' . $this->stats['queries'][$type]['u'] . ' d' . $this->stats['queries'][$type]['d'];
    }

    private function getAllStatsAsTable(): void
    {
        // Total Purge + Load queries
        $queries_total = CMbArray::sumArraysByKey([$this->stats['queries']['purge'], $this->stats['queries']['load']]);

        $io = new SymfonyStyle($this->input, $this->output);
        $io->horizontalTable(
            ['<comment>Total</comment>', '<fg=#c0392b>Purge</>', 'Load'],
            [
                [
                    $this->stats['time']['purge'] + $this->stats['time']['load'] . ' sec',
                    $this->stats['time']['purge'] . ' sec',
                    $this->stats['time']['load'] . ' sec',
                ],
                [
                    $this->stats['references']['purge'] + $this->stats['references']['load'] . ' refs',
                    $this->stats['references']['purge'] . ' refs',
                    $this->stats['references']['load'] . ' refs',
                ],
                [
                    's' . $queries_total['s'] . ' i' . $queries_total['i']
                    . ' u' . $queries_total['u'] . ' d' . $queries_total['d'],
                    $this->getQueriesStatsAsString('purge'),
                    $this->getQueriesStatsAsString('load'),
                ],
            ]
        );
    }

    private function setStats(string $type, float $time, int $refs, string $queries): void
    {
        $this->stats['time'][$type]       += $time;
        $this->stats['references'][$type] += $refs;
        $this->stats['queries'][$type]    = CMbArray::sumArraysByKey(
            [$this->stats['queries'][$type], $this->getQueriesStatsAsArray($queries)]
        );
    }

    /**
     * @return FixturesLoader
     */
    private function orderFixtures(): FixturesLoader
    {
        $this->fixtures_ordered = [];
        /** @var Fixtures $fixtures */
        foreach ($this->fixtures as $fixtures_class => $fixtures) {
            if (is_subclass_of($fixtures, GroupFixturesInterface::class)) {
                $group = $fixtures->getGroup();

                $group_name = $group[0];
                if (!isset($index) || isset($group[1])) {
                    $index = $group[1] ?? 0;
                }

                // New group
                if (!array_key_exists($group_name, $this->fixtures_ordered)) {
                    $this->fixtures_ordered[$group_name][$index] = $fixtures_class;
                    continue;
                }

                // Existing group
                $index = array_key_exists($index, $this->fixtures_ordered[$group_name]) ? $index + 1 : $index;

                $this->fixtures_ordered[$group_name][$index] = $fixtures_class;
            } else {
                // not grouped
                $this->fixtures_ordered[] = $fixtures_class;
            }
        }

        // sort & ungroup
        ksort($this->fixtures_ordered);
        foreach ($this->fixtures_ordered as $group_name => $fixtures_grouped) {
            if (is_array($fixtures_grouped)) {
                krsort($fixtures_grouped);
                foreach ($fixtures_grouped as $fixtures_in_group) {
                    $this->fixtures_ordered[] = $fixtures_in_group;
                }
                unset($this->fixtures_ordered[$group_name]);
            }
        }

        // replace references
        foreach ($this->fixtures_ordered as $key => $fixtures_class) {
            $this->fixtures_ordered[$key] = $this->fixtures[$fixtures_class];
        }

        // reassign
        $this->fixtures = $this->fixtures_ordered;

        return $this;
    }

    /**
     * @throws ReflectionException
     */
    private function findFixtures(): FixturesLoader
    {
        $this->fixtures = (new FixturesFinder($this->path, $this->groups, $this->namespace))->find();
        if (empty($this->fixtures)) {
            $this->output->writeln('<info>No fixtures to load !</info>');
            exit(1);
        }

        return $this;
    }

    private function bootstrap(): FixturesLoader
    {
        // Active datasource logging
        CSQLDataSource::$log = true;

        CFile::registerPrivateDirectory();

        return $this;
    }

    private function confirm(): FixturesLoader
    {
        if (!$this->is_append) {
            if (
                !$this->io->confirm(
                    'Careful, database will be purged. Do you want to continue?'
                )
            ) {
                exit(1);
            }
        }

        return $this;
    }

    /**
     * @param array $groups
     */
    public function setGroups(array $groups): void
    {
        $this->groups = $groups;
    }

    /**
     * @param string $namespace
     */
    public function setNameSpace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    /**
     * @return array
     */
    public function getFixtures(): array
    {
        return $this->fixtures;
    }

    /**
     * @return array
     */
    public function getFixturesOrdered(): array
    {
        return $this->fixtures_ordered;
    }
}
