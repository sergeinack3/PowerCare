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
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeployRelease extends MediboardCommand
{
    /** @var string */
    public const RSYNC_EXCLUDE_FILE = 'cli/resources/rsync-deploy.exclude';
    public const RSYNC_INCLUDE_FILE = 'cli/resources/rsync-deploy.include';

    public const BRANCH_MASTER = 'master';

    /** @var string */
    protected $root_dir;

    /** @var string */
    protected $ssh_deploy;

    /** @var string */
    protected $path_deploy;

    /** @var string */
    protected $branch_name;

    /** @var bool|string|string[]|null */
    protected $remote;

    /** @var bool|string|string[]|null */
    private $current_ref;

    /** @var OutputInterface */
    private $output;

    /** @var InputInterface */
    private $input;

    /**
     * @see parent::configure()
     */
    protected function configure(): void
    {
        $this
            ->setName('ox-ci:deploy-release')
            ->setDescription('Deploy release on ox-deploy server')
            ->addOption(
                'ssh_deploy',
                's',
                InputOption::VALUE_REQUIRED,
                'SSH Serveur deployement'
            )->addOption(
                'path_deploy',
                'p',
                InputOption::VALUE_REQUIRED,
                'SSH destination'
            )->addOption(
                'branch_name',
                'b',
                InputOption::VALUE_REQUIRED,
                'The branch name'
            )->addOption(
                'current_ref',
                'c',
                InputOption::VALUE_REQUIRED,
                'The current ref (commit sha)'
            )->addOption(
                'remote',
                'r',
                InputOption::VALUE_REQUIRED,
                'Git lab mb remote'
            );
    }

    /**
     * @see parent::showHeader()
     */
    protected function showHeader(): void
    {
        $this->out($this->output, '<fg=red;bg=black>Deploy Release</fg=red;bg=black>');
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
        $this->output      = $output;
        $this->input       = $input;
        $this->root_dir    = dirname(__DIR__, 4);
        $this->path_deploy = $this->input->getOption('path_deploy');
        $this->ssh_deploy  = $this->input->getOption('ssh_deploy');
        $this->branch_name = $this->input->getOption('branch_name');
        $this->remote      = $this->input->getOption('remote');
        $this->current_ref = $this->input->getOption('current_ref');

        $this->showHeader();
        if ($this->isLastRef()) {
            $this->deploy();
        }

        return self::SUCCESS;
    }

    /**
     * When retry a edployment job, ref sould be out of date
     * and must not be rsync in environement
     *
     * @throws RuntimeException
     */
    private function isLastRef(): bool
    {
        $cmd   = [];
        $cmd[] = 'git ls-remote';
        $cmd[] = $this->remote; // remote
        $cmd[] = 'refs/heads/' . $this->branch_name; // ref
        $cmd[] = '| cut -f 1';
        $cmd   = implode(' ', $cmd);
        $this->output($cmd);

        exec($cmd, $result, $state);

        if ($state !== 0) {
            throw new RuntimeException("Error occurred during checking refs is out of date");
        }

        $last_ref_on_branch = $result[0];
        if ($last_ref_on_branch !== $this->current_ref) {
            $this->output("Current ref {$this->current_ref} is not the last one ({$last_ref_on_branch})");

            return false;
        }

        return true;
    }

    private function getBranchNameFormatted(): string
    {
        if ($this->branch_name === static::BRANCH_MASTER) {
            return $this->branch_name;
        }

        return explode('/', $this->branch_name)[1];
    }


    private function deploy(): void
    {
        // Dest
        $dest = $this->path_deploy . $this->getBranchNameFormatted();

        // mkdir
        $cmd   = [];
        $cmd[] = "ssh";
        $cmd[] = $this->ssh_deploy;
        $cmd[] = "mkdir";
        $cmd[] = "--parents"; // no error if existing, make parent directories as needed
        $cmd[] = '--verbose';
        $cmd[] = $dest;
        $cmd   = implode(' ', $cmd);
        $this->output($cmd);

        exec($cmd, $result, $state);
        $this->output('Output : ' . implode(PHP_EOL, $result));

        if ($state !== 0) {
            throw new RuntimeException("Error occurred during create directory {$dest}");
        }

        // rsync
        $cmd   = [];
        $cmd[] = 'rsync';
        $cmd[] = '--recursive'; // recurse into directories
        $cmd[] = '--compress '; // compress file data during the transfer
        $cmd[] = '--cvs-exclude '; // auto-ignore files the same way CVS does
        $cmd[] = '--human-readable'; // output numbers in a human-readable format
        $cmd[] = '--delete'; // delete extraneous files from destination dirs
        $cmd[] = '--stats'; // give some file-transfer stats
        $cmd[] = '--exclude-from ' . $this->getExcludeFile(); // read exclude patterns from FILE
        $cmd[] = '--include-from ' . $this->getIncludeFile(); // read include patterns from FILE
        $cmd[] = './'; // from
        $cmd[] = $this->ssh_deploy . ':' . $dest; // dest
        $cmd   = implode(' ', $cmd);
        $this->output($cmd);

        $result = [];
        exec($cmd, $result, $state);
        $this->output('Output : ' . implode(PHP_EOL, $result));

        if ($state !== 0) {
            throw new RuntimeException("Error occurred during RSYNC {$dest}");
        }
    }

    private function getExcludeFile(): string
    {
        return $this->root_dir . DIRECTORY_SEPARATOR . self::RSYNC_EXCLUDE_FILE;
    }

    private function getIncludeFile(): string
    {
        return $this->root_dir . DIRECTORY_SEPARATOR . self::RSYNC_INCLUDE_FILE;
    }

    private function output(string $msg): void
    {
        $this->out($this->output, $msg);
    }
}
