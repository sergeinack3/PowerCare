<?php

/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Console\CI;

use Exception;
use Ox\Cli\MediboardCommand;
use Ox\Core\Kernel\Routing\RouteManager;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class APICoverageCollect extends MediboardCommand
{
    protected const CONFIG_FILE            = 'includes/config.php';
    protected const NEWMAN_REPORT_SUMMARY  = 'tmp/api-test-report-summary.json';
    protected const NEWMAN_REPORT_INPUT    = 'tmp/api-test-report.log';
    protected const COVERAGE_JSON_OUTPUT   = 'tmp/api-test-coverage.json';

    /** @var SymfonyStyle */
    protected $io;

    /** @var string */
    protected $path;

    /** @var Filesystem */
    protected $fs;

    /** @var array|null */
    protected $result;

    /** @var array */
    private $config = null;

    /**
     * @see parent::configure()
     */
    protected function configure(): void
    {
        $this
            ->setName('ox-ci:api-coverage-collect')
            ->setDescription('Collects an API Coverage report from a Newman run log.')
            ->addArgument(
                'file',
                InputArgument::OPTIONAL,
                'Newman run output file path',
                self::NEWMAN_REPORT_INPUT
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws FileNotFoundException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->input  = $input;
        $this->io     = new SymfonyStyle($this->input, $this->output);
        $this->fs     = new Filesystem();

        $this->path = dirname(__DIR__, 4);

        $this->loadConfigurationValues();

        $required_tests = [];

        $allRoutesFilePath = $this->path . '/includes/all_routes.yml';
        if (!$this->fs->exists($allRoutesFilePath)) {
            throw new FileNotFoundException('Routes Yaml File not found : ' . $allRoutesFilePath);
        }

        $routes = Yaml::parseFile($allRoutesFilePath);
        foreach ($routes as $routeName => $routeData) {
            foreach ($routeData['methods'] as $method) {
                $required_tests[] = [
                    'name'      => $routeName,
                    'method'    => $method,
                ];
            }
        }

        $apiTestReportLog = $this->path . '/' . $input->getArgument('file');
        if (!$this->fs->exists($apiTestReportLog)) {
            throw new FileNotFoundException('API Test Report Log File not found : ' . $apiTestReportLog);
        }

        $regex = "/^\s+("
            . implode('|', RouteManager::ALLOWED_METHODS)
            . ")\s+(((\w+:\/\/\S+)|(\w+[\.:]\w+\S+))[^\s,\.])/m";

        preg_match_all($regex, file_get_contents($apiTestReportLog), $matches);

        $performed_tests = [];

        if (!empty($matches)) {
            foreach ($matches[0] as $match) {

                $match = str_replace($this->getConfig('base_url'), '', $match);

                $call   = explode(' ', trim($match));
                $method = $call[0];
                $url    = parse_url($call[1]);

                $output = new BufferedOutput();

                $command = $this->getApplication()->find('router:match');

                $result = $command->run(
                    new ArrayInput(
                        [
                            'command'   => $command->getName(),
                            'path_info' => $url['path'],
                            '--method'  => $method,
                        ]
                    ),
                    $output
                );

                if ($result === self::SUCCESS) {
                    $content = $output->fetch();

                    $name   = $this->getPropertyFromRouterMatch($content, 'Route Name');
                    $method = $this->getPropertyFromRouterMatch($content, 'Method');

                    if (null !== $name && null !== $method) {
                        $performed_tests[] = [
                            'name'      => $name,
                            'method'    => $method
                        ];
                    }
                }
            }
        }

        $performed_tests = array_unique($performed_tests, SORT_REGULAR);

        $covered_tests = array_map(
            'unserialize',
            array_intersect(
                array_map('serialize', $required_tests),
                array_map('serialize', $performed_tests)
            )
        );

        $uncovered_tests = array_map(
            'unserialize',
            array_diff(
                array_map('serialize', $required_tests),
                array_map('serialize', $performed_tests)
            )
        );

        $this->result = [
            'tests' => [
                'coverage' => round(count($covered_tests) / count($required_tests) * 100, 2),
                'count' => [
                    'all' => count($required_tests),
                    'covered'   => count($covered_tests),
                    'uncovered' => count($uncovered_tests),
                ],
                'data' => [
                    'covered'   => $covered_tests,
                    'uncovered' => $uncovered_tests,
                ]
            ],
            'routes' => [
                'count' => [
                    'all' => count($routes)
                ]
            ]
        ];

        $this->io->success('Covered API Routes/Methods : ' . count($covered_tests));
        $this->io->table(
            ['Method', 'Route'],
            $covered_tests
        );

        $this->io->warning('Uncovered API Routes/Methods : ' . count($uncovered_tests));
        $this->io->table(
            ['Method', 'Route'],
            $uncovered_tests
        );

        $outputFilePath = $this->path . '/' . self::COVERAGE_JSON_OUTPUT;
        $this->fs->dumpFile($outputFilePath, json_encode($this->result));
        $this->io->note('API Tests Coverage exported to : ' . $outputFilePath);

        $this->io->table(
            ['Total', 'Covered', 'Uncovered'],
            [$this->result['tests']['count']],
        );

        return $this->hasFailedAssertions();
    }

    /**
     * @return int
     *
     * @throws FileNotFoundException
     */
    private function hasFailedAssertions(): int
    {
        $summaryPath = $this->path . '/' . self::NEWMAN_REPORT_SUMMARY;
        if (!$this->fs->exists($summaryPath)) {
            throw new FileNotFoundException('Newman Json Report Summary File not found : ' . $summaryPath);
        }

        $summary  = json_decode(file_get_contents($summaryPath), true);
        $failures = $summary['Run']['Stats']['Assertions']['failed'];

        if ($failures > 0) {
            $this->io->error($failures . ' assertions failed during this run !');
            return self::FAILURE;
        }

        $this->io->success('All assertions were successful during this run.');

        return self::SUCCESS;
    }

    /**
     * @throws Exception
     */
    private function loadConfigurationValues(): void
    {
        $configPath = $this->path . '/' . self::CONFIG_FILE;
        if ($this->fs->exists($configPath)) {
            global $dPconfig;
            require $configPath;
            $this->config = $dPconfig;
        } else {
            throw new Exception('Configuration file is missing : ' . $configPath);
        }
    }

    /**
     * @param string $key
     *
     * @return mixed|string
     */
    private function getConfig(string $key)
    {
        return $this->config[$key] ?? '';
    }

    /**
     * @param string $content
     * @param string $property
     *
     * @return string|null
     */
    private function getPropertyFromRouterMatch(string $content, string $property): ?string
    {
        if (preg_match('/^\|\s+' . $property . '\s+\|\s+(\w+)\s+\|$/m', $content, $match)) {
            return end($match);
        }
        return null;
    }
}
