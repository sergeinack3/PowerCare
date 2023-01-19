<?php

/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Console;

use Cz\Git\GitException;
use Cz\Git\GitRepository;
use Exception;
use Ox\Cli\MediboardCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;

/**
 * Class InspectCode
 */
class InspectCode extends MediboardCommand
{

    /** @var string */
    public const TOOL_PHPSTAN = 'phpStan';

    /** @var string */
    public const TOOL_PHPCS = 'codeSniffer';

    /** @var string */
    public const TOOL_PHPCBF = 'codeBeautifierFixer';

    /** @var string */
    public const TOOL_PHPCOMPAT = 'phpCompatibility';

    /** @var string */
    public const TOOL_PHPLINT = 'phpParallelLint';

    /** @var string */
    public const TOOL_ALL = '*';

    /** @var array */
    public const TOOLS = [
        self::TOOL_PHPSTAN,
        self::TOOL_PHPCS,
        self::TOOL_PHPCBF,
        self::TOOL_PHPCOMPAT,
        self::TOOL_PHPLINT,
    ];

    /** @var array */
    public const TOOLS_DESC = [
        self::TOOL_PHPSTAN   => 'PHPStan             => Find Bugs In Your Code Without Writing Tests! (default)',
        self::TOOL_PHPCS     => 'CodeSniffer         => Detect violations of a defined coding standard',
        self::TOOL_PHPCBF    => 'CodeBeautifierFixer => Fixes violations of a defined coding standard',
        self::TOOL_PHPCOMPAT => 'phpCompatibility    => Check for PHP cross-version compatibility',
        self::TOOL_PHPLINT   => 'phpParallelLint     => Check syntax of PHP files',
    ];

    /** @var string */
    public const CONTEXT_PATH = 'path';

    /** @var string */
    public const CONTEXT_COMMIT = 'commit';

    /** @var string */
    public const CONTEXT_WC = 'wc';

    /** @var array */
    public const CONTEXTS = [
        self::CONTEXT_PATH,
        self::CONTEXT_COMMIT,
        self::CONTEXT_WC,
    ];

    /** @var array */
    public const CONTEXT_DESC = [
        self::CONTEXT_PATH   => 'Paths to files or directories',
        self::CONTEXT_COMMIT => 'Changed files from a commit hash',
        self::CONTEXT_WC     => 'All changed files in working copy',
    ];

    /** @var OutputInterface */
    protected $output;

    /** @var InputInterface */
    protected $input;

    /** @var string */
    protected $path;

    /** @var string */
    protected $command;

    /** @var QuestionHelper */
    protected $question_helper;


    /**
     * @see parent::configure()
     */
    protected function configure(): void
    {
        $this
            ->setName('ox-inspect:code')
            ->setDescription('Launch static analysis tools (phpstan, code sniffer ...')
            ->addOption(
                'path',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Working copy root',
                dirname(__DIR__, 3) . '/'
            );
    }

    /**
     * @see parent::showHeader()
     */
    protected function showHeader(): void
    {
        $this->output->writeln(
            <<<EOT
<fg=blue;bg=black>
  _____ _    _     ______          _          _____                                             
 / ___ \ \  / /   / _____)        | |        (_____)                            _               
| |   | \ \/ /   | /      ___   _ | | ____      _   ____   ___ ____   ____ ____| |_  ___   ____ 
| |   | |)  (    | |     / _ \ / || |/ _  )    | | |  _ \ /___)  _ \ / _  ) ___)  _)/ _ \ / ___)
| |___| / /\ \   | \____| |_| ( (_| ( (/ /    _| |_| | | |___ | | | ( (/ ( (___| |_| |_| | |    
 \_____/_/  \_\   \______)___/ \____|\____)  (_____)_| |_(___/| ||_/ \____)____)\___)___/|_|    
                                                              |_|                               
</fg=blue;bg=black>
EOT
        );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws InvalidArgumentException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output          = $output;
        $this->input           = $input;
        $this->question_helper = $this->getHelper('question');
        $this->path            = $input->getOption('path');
        $this->showHeader();

        if (!is_dir($this->path)) {
            throw new InvalidArgumentException("'$this->path' is not a valid directory.");
        }

        if (!$this->askQuestions()) {
            $this->output->writeln($this->command);

            return self::INVALID;
        }

        $process = new Process($this->getCommandAsArray());
        $process->setTimeout(300);
        $process->run(static function ($type, $buffer): void {
            echo $buffer;
        });

        return self::SUCCESS;
    }


    /**
     * @return mixed
     * @throws GitException
     */
    private function askQuestions()
    {
        $tools = array_values(self::TOOLS_DESC);
        $tool  = $this->ask(new ChoiceQuestion('Choice an inspector (default phpStan) ?', $tools, 0));
        $tool  = array_search($tool, self::TOOLS_DESC, true);
        $this->showChoice($tool);

        $contexts = array_values(self::CONTEXT_DESC);
        $context  = $this->ask(new ChoiceQuestion("Choice a context inspection (default path) ?", $contexts, 0));
        $context  = array_search($context, self::CONTEXT_DESC, true);
        $this->showChoice($context);

        $path = null;
        if ($context === 'path') {
            $path = $this->ask(new Question('Enter the path (files or directory) ?', '.'));
            $this->showChoice($path);
        }

        $hash = null;
        if ($context === 'commit') {
            $wc             = new GitRepository($this->path);
            $last_commit_id = $wc->getLastCommitId();
            $hash           = $this->ask(
                new Question("Enter the revision number (default last commit) ?", $last_commit_id)
            );
            $this->showChoice($hash);
        }

        $options = $this->ask(new Question('Enter custom options (ex: --verbose) ?', ''));
        if ($options) {
            $this->showChoice($options);
        }

        // command
        $this->buildCommand($tool, $context, $path, $hash, $options);

        // confirm or display
        return $this->ask(new ConfirmationQuestion("Confirm execution [Y/n] ?", true));
    }

    /**
     * @param string $tool
     * @param string $context
     * @param string|null $path
     * @param string|null $hash
     * @param string|null $options
     *
     * @return void Command to display confirmation
     */
    private function buildCommand(string $tool, string $context, ?string $path, ?string $hash, ?string $options): void
    {
        $this->output->writeln('Building command ...');

        // context
        switch ($context) {
            case self::CONTEXT_PATH:
                $paths = explode(' ', $path);
                break;
            case self::CONTEXT_COMMIT:
                exec("git diff-tree --no-commit-id --name-only -r --diff-filter=d {$hash}", $paths);
                break;
            case self::CONTEXT_WC:
                exec("git ls-files -mo --exclude-standard", $paths);
                break;
            default:
                $paths = [];
                break;
        }

        // tool
        $paths_placeholder = '<' . count($paths) . ' paths>';
        switch ($tool) {
            case self::TOOL_PHPSTAN:
                $command = "vendor/bin/phpstan analyse {$paths_placeholder} --configuration phpstan.neon";
                break;
            case self::TOOL_PHPCS:
                $command = "vendor/bin/phpcs -p {$paths_placeholder} "
                    . "--report=full --standard=vendor/openxtrem/coding-standard/OpenxtremCodingStandard/ruleset.xml";
                break;
            case self::TOOL_PHPCBF:
                $command = "vendor/bin/phpcbf -p {$paths_placeholder} "
                    . "-p --standard=vendor/openxtrem/coding-standard/OpenxtremCodingStandard/ruleset.xml";
                break;
            case self::TOOL_PHPCOMPAT:
                $command = "vendor/bin/phpcs -p {$paths_placeholder} "
                    . "--report=full --standard=dev/CodeSniffer/PHPC/ruleset.xml --extensions=php";
                break;
            case self::TOOL_PHPLINT:
                $command = "vendor/bin/parallel-lint {$paths_placeholder}";
                break;
            default:
                $command = null;
                break;
        }

        // add custom options
        if ($options) {
            $command .= ' ' . $options;
        }

        // output
        $this->output->writeln('Your command : <fg=green;bg=black>' . $command . '</fg=green;bg=black>');

        $this->command = str_replace($paths_placeholder, implode(' ', $paths), $command);
    }

    /**
     * @param Question $question
     *
     * @return mixed
     */
    private function ask(Question $question)
    {
        return $this->question_helper->ask($this->input, $this->output, $question);
    }

    /**
     * @param string $answer
     *
     * @return void
     */
    private function showChoice(string $answer): void
    {
        $this->output->writeln('Your choice : <fg=green;bg=black>' . $answer . '</fg=green;bg=black>');
    }

    /**
     * @return array
     */
    private function getCommandAsArray(): array
    {
        return explode(' ', $this->command);
    }
}
