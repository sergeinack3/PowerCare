<?php

/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Console;

use Exception;
use Ox\Cli\Benchmark;
use Ox\Cli\CommandLinePDO;
use Ox\Cli\MediboardCommand;
use Ox\Status\Models\PathAccess;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Process\Process;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class Diagnose extends MediboardCommand
{
    protected const ALLROUTES_FILE            = 'includes/all_routes.yml';
    protected const CLASSMAP_FILE             = 'includes/classmap.php';
    protected const CLASSREF_FILE             = 'includes/classref.php';
    protected const CONFIG_FILE               = 'includes/config.php';
    protected const DISK_USAGE_THRESHOLD      = 90;
    protected const PHP_PROCESSOR_THRESHOLD   = 0.8;
    protected const PHP_FILESYSTEM_THRESHOLD  = 0.5;
    protected const HTTP_TIMEOUT              = 2;
    protected const HTTP_TIMEOUT_CI           = 30;
    protected const LEGACY_ACTIONS_FILE       = 'includes/legacy_actions.php';
    protected const TEMP_FOLDER               = 'tmp';
    protected const JSON_REPORT               = self::TEMP_FOLDER . '/ox-diagnose.json';

    /** @var OutputInterface */
    protected $output;

    /** @var InputInterface */
    protected $input;

    /** @var SymfonyStyle */
    protected $io;

    /** @var string */
    protected $path;

    /** @var bool */
    protected $json;

    /** @var bool */
    protected $ci_mode;

    /** @var Filesystem */
    protected $fs;

    /** @var array */
    private $testSuite = [];

    /** @var int */
    private $errorCount = 0;

    /** @var int */
    private $warningCount = 0;

    /** @var array */
    private $config = null;

    /**
     * @see parent::configure()
     */
    protected function configure(): void
    {
        $this
            ->setName('ox-install:diagnose')
            ->setDescription('Runs a diagnosis of a Mediboard instance configuration')
            ->addOption(
                'path',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Working copy root',
                dirname(__DIR__, 3) . '/'
            )->addOption(
                'json',
                null,
                InputOption::VALUE_NONE,
                'Exports diagnosis report as a JSON file',
            )->addOption(
                'ci-mode',
                null,
                InputOption::VALUE_OPTIONAL,
                'Use CI Mode',
                false
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output  = $output;
        $this->input   = $input;
        $this->io      = new SymfonyStyle($this->input, $this->output);
        $this->path    = $input->getOption('path');
        $this->json    = $input->getOption('json');
        $this->ci_mode = $input->getOption('ci-mode');
        $this->fs      = new Filesystem();

        $this->showHeader();

        if (!is_dir($this->path)) {
            throw new InvalidArgumentException("'$this->path' is not a valid directory.");
        }

        $this->checkComposer();

        $this->checkStructure();
        $this->checkTemporaryFolder();
        $this->checkConfigurationFile();
        $this->checkPostInstallFiles();
        $this->checkDatabase();
        $this->checkAPCU();
        $this->checkRedis();
        $this->checkWebServer();
        $this->checkDiskUsage();
        $this->checkBenchmark();

        $this->renderTestSuite();

        return empty($this->errorCount) ? self::SUCCESS : self::FAILURE;
    }

    private function checkDiskUsage(): void
    {
        $usage  = 'N/A';

        $ps = new Process(['df', '--output=pcent', '-m', '.']);
        $ps->run();

        if ($ps->isSuccessful()) {
            $output = explode(PHP_EOL, $ps->getOutput());
            if (count($output) > 1) {
                $usage = intval(str_replace('%', '', trim($output[1])));
            }
        }

        $this->markTest(
            'Disk usage (raises a warning if over ' . self::DISK_USAGE_THRESHOLD . '%)',
            $usage < self::DISK_USAGE_THRESHOLD,
            'Disk usage is ' . (is_integer($usage) ? 'high : ' . $usage . '%' : 'unavailable : ' . $usage)
        );
    }

    private function checkComposer(): void
    {
        $result = false;

        $ps = new Process(['composer', 'install', '--dry-run', '--no-ansi', '--no-interaction']);
        $ps->run();

        if ($ps->isSuccessful()) {
            /* Composer writes a lot of info data to the stderr output */
            $output = explode(PHP_EOL, $ps->getErrorOutput());
            if (!empty($output)) {
                $counter = 0;
                while ($result === false && $counter < count($output)) {
                    if ($output[$counter] === "Nothing to install, update or remove") {
                        $result = true;
                    }
                    $counter++;
                }
            }
        }

        $this->markTest(
            'Composer dependencies up to date',
            $result,
            'Composer dependencies are available for update',
            false
        );
    }

    private function checkStructure(): void
    {
        $folders = (new PathAccess())->getAll();
        /** @var PathAccess $folder */
        foreach ($folders as $folder) {
            $this->markTest(
                'Application mandatory folder ' . $folder->path . ' is accessible',
                true === $folder->check(),
                'Application mandatory folder is missing or not writable : ' . $folder->path
            );
        }
    }

    private function checkDatabase(): void
    {
        $this->markTest(
            'Standard datasource is defined',
            array_key_exists('std', $this->getConfig('db') ?: []),
            'The standard datasource is not defined in configuration.'
        );

        $databases = $this->getConfig('db');
        $std       = is_array($databases) ? $databases['std'] ?? null : null;

        $result = true;
        $error = false;

        try {
            $pdo = new CommandLinePDO(
                $std['dbhost'] ?? '',
                $std['dbuser'] ?? '',
                $std['dbpass'] ?? '',
            );

            $this->markTest(
                'Standard datasource exists',
                $pdo->isDatabaseExists($std['dbname']),
                'Standard datasource does not exist within database named : ' . $std['dbname']
            );
        } catch (Exception $e) {
            $result = false;
            $error  = $e->getMessage();
        }

        $this->markTest(
            'Connection to database server',
            $result,
            $error
        );
    }

    private function checkConfigurationFile(): void
    {
        $this->markTest(
            'Configuration file exists',
            $this->fs->exists($this->path . self::CONFIG_FILE),
            'Configuration file is missing : ' . $this->path . self::CONFIG_FILE
        );

        $this->loadConfigurationValues();

        $this->markTest(
            'Root directory is valid',
            $this->fs->exists($this->getConfig('root_dir')),
            'Root directory is invalid or missing : ' . $this->getConfig('root_dir')
        );

        $this->markTest(
            'Config DB is enabled',
            $this->getConfig('config_db') === '1',
            'The value of config_db must be 1 (string) : ' . $this->getConfig('config_db')
        );
    }

    private function checkPostInstallFiles(): void
    {
        $files = [
            self::ALLROUTES_FILE,
            self::CLASSMAP_FILE,
            self::CLASSREF_FILE,
            self::LEGACY_ACTIONS_FILE,
        ];

        foreach ($files as $file) {
            $this->markTest(
                'File ' . $file . ' exists',
                $this->fs->exists($this->path . $file),
                'File is missing : ' . $this->path . $file
            );
        }
    }

    private function checkTemporaryFolder(): void
    {
        $this->markTest(
            'Temporary folder exists',
            $this->fs->exists($this->path . self::TEMP_FOLDER),
            'Temporary folder is missing : ' . $this->path . self::TEMP_FOLDER
        );

        $isWritable = true;
        try {
            $tempFile = $this->path . self::TEMP_FOLDER . '/diagnose.txt';
            $this->fs->touch($tempFile);
            $this->fs->remove($tempFile);
        } catch (Exception $e) {
            $isWritable = false;
        }

        $this->markTest(
            'Temporary folder is writable',
            $isWritable,
            'Temporary folder is not writable : ' . $this->path . self::TEMP_FOLDER
        );
    }

    private function checkAPCU(): void
    {
        $apcEnable    = ini_get('apc.enabled');
        $apcEnableCli = ini_get('apc.enable_cli');

        $this->markTest(
            'PHP APCU extension',
            extension_loaded('apcu'),
            'PHP APCU extension is not loaded.'
        );

        $this->markTest(
            'PHP Ini apc.enabled',
            '1' === $apcEnable,
            'Value for PHP Ini apc.enabled must be 1 : ' . $apcEnable
        );

        $this->markTest(
            'PHP Ini apc.enable_cli',
            '1' === $apcEnableCli,
            'Value for PHP Ini apc.enable_cli must be 1 : ' . $apcEnableCli
        );

        $this->markTest(
            'APCU used as Shared Memory',
            'apcu' === $this->getConfig('shared_memory'),
            'APCU is not used as Shared Memory in config'
        );
    }

    private function checkRedis(): void
    {
        $this->markTest(
            'Redis used as Distributed Shared Memory',
            'redis' === $this->getConfig('shared_memory_distributed'),
            'Redis is not used as Distributed Shared Memory in config'
        );

        $redisEndpoint = explode(':', $this->getConfig('shared_memory_params'));

        if (count($redisEndpoint) === 2) {
            $command = [
                'redis-cli',
                '-h',
                $redisEndpoint[0],
                '-p',
                $redisEndpoint[1],
                'ping',
            ];

            $ps = new Process($command);
            $ps->run();

            $this->markTest(
                'Connection to Redis server',
                $ps->isSuccessful(),
                $ps->getErrorOutput()
            );
        }
    }

    private function checkWebServer(): void
    {
        $routes = [
            'Base URL' => $this->getConfig('base_url'),
            'API URL'  => $this->getConfig('base_url') . '/api/status',
        ];

        $httpClient = HttpClient::create();

        foreach ($routes as $routeName => $routeUrl) {
            $result = false;
            $error  = false;

            try {
                $response = $httpClient->request(
                    'GET',
                    $routeUrl,
                    [
                        'timeout' => $this->ci_mode ? self::HTTP_TIMEOUT_CI : self::HTTP_TIMEOUT
                    ]
                );
                if ($response->getStatusCode() === 200) {
                    $result = true;
                } else {
                    $error = 'Exit code : ' . $response->getStatusCode() . PHP_EOL . $response->getContent();
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }

            $this->markTest(
                'HTTP Request to ' . $routeName . ' : ' . $routeUrl,
                $result,
                $error
            );
        }
    }

    private function checkBenchmark(): void
    {
        $benchmarks = [
            [
                'name'      => 'Benchmark PHP Filesystem',
                'pattern'   => 'testFilesystem',
                'threshold' => self::PHP_FILESYSTEM_THRESHOLD,
                'xdebug'    => false,
            ],
            [
                'name'      => 'Benchmark PHP Processor',
                'pattern'   => 'testProcessor',
                'threshold' => self::PHP_PROCESSOR_THRESHOLD,
                'xdebug'    => true,
            ],
        ];

        foreach ($benchmarks as $benchmark) {
            $executionTime = Benchmark::run($benchmark['pattern']);
            if ($executionTime > $benchmark['threshold']) {
                $message = 'Expected under ' . $benchmark['threshold'] . 's but got ' . $executionTime . 's';
                if (true === $benchmark['xdebug'] && extension_loaded('xdebug')) {
                    $message .= PHP_EOL . '<comment>Xdebug is loaded. '
                        . 'Consider running this command prefixed with XDEBUG_MODE=off.</comment>';
                }
            } else {
                $message = $executionTime . 's';
            }

            $this->markTest(
                $benchmark['name'] . ' (threshold: ' . $benchmark['threshold'] . 's)',
                $executionTime < $benchmark['threshold'],
                $message,
                $executionTime > $benchmark['threshold'] * 2,
                true
            );
        }
    }

    private function loadConfigurationValues(): void
    {
        if ($this->fs->exists($this->path . self::CONFIG_FILE)) {
            global $dPconfig;
            require $this->path . self::CONFIG_FILE;
            $this->config = $dPconfig;
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
     * @param string $description
     * @param bool $evaluation
     * @param string|null $message
     * @param bool $strict
     * @param bool $displayOnSuccess
     */
    private function markTest(string $description, bool $evaluation, ?string $message, bool $strict = true, bool $displayOnSuccess = false): void
    {
        if (false === $evaluation) {
            if (true === $strict) {
                $this->errorCount++;
            } else {
                $this->warningCount++;
            }
        }

        $this->testSuite[] = [
            'Description' => $description,
            'Result'      => $evaluation ?
                '<info>OK</info>' : ($strict ? '<error>ERROR</error>' : '<comment>WARNING</comment>'),
            'Reason'      => (!$evaluation || $displayOnSuccess) ? $message : false,
        ];
    }

    private function renderTestSuite(): void
    {
        $this->io->table(
            ['Description', 'Result', 'Reason'],
            $this->testSuite
        );

        if ($this->errorCount === 0 && $this->warningCount === 0) {
            $this->io->success('Diagnose ran successfully !');
        } else {
            if ($this->errorCount) {
                $this->io->error('Diagnose has reported errors : ' . $this->errorCount);
            }
            if ($this->warningCount) {
                $this->io->warning('Diagnose has reported warnings : ' . $this->warningCount);
            }
        }

        if (true === $this->json) {
            if ($this->fs->exists($this->path . self::TEMP_FOLDER)) {
                $this->fs->dumpFile(
                    $this->path . self::JSON_REPORT,
                    strip_tags(
                        json_encode(
                            [
                                'errors'   => $this->errorCount,
                                'warnings' => $this->warningCount,
                                'tests'    => $this->testSuite,
                            ]
                        )
                    )
                );
            }
        }
    }


    protected function showHeader(): void
    {
        $this->io->writeln(
            <<<EOT
    ____  _                                 
   / __ \(_)___ _____ _____  ____  ________ 
  / / / / / __ `/ __ `/ __ \/ __ \/ ___/ _ \
 / /_/ / / /_/ / /_/ / / / / /_/ (__  )  __/
/_____/_/\__,_/\__, /_/ /_/\____/____/\___/ 
              /____/                        
EOT
        );
    }
}
