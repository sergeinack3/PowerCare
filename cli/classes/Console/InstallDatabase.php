<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Console;

use Exception;
use Ox\Cli\CommandLinePDO;
use Ox\Cli\MediboardCommand;
use Ox\Core\CAppUI;
use Ox\Core\CMbConfig;
use Ox\Mediboard\Admin\CUser;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class InstallDatabase
 *
 * @package Ox\Cli\Console
 */
class InstallDatabase extends MediboardCommand
{
    /** @var OutputInterface */
    protected $output;

    /** @var InputInterface */
    protected $input;

    /** @var SymfonyStyle */
    protected $io;

    /** @var string */
    protected $path;

    /** @var QuestionHelper */
    protected $question_helper;

    /** @var array */
    protected $params = [];

    /** @var bool */
    protected $config;

    /** @var bool */
    protected $delete;

    /** @var CommandLinePDO */
    protected $pdo;

    /**
     * @see parent::configure()
     */
    protected function configure(): void
    {
        $this
            ->setName('ox-install:database')
            ->setDescription('Install OX database')
            ->addOption(
                'path',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Working copy root',
                dirname(__DIR__, 3) . '/'
            )->addOption(
                'config',
                'c',
                InputOption::VALUE_NONE,
                'Use parameters from configuration file',
            )->addOption(
                'delete',
                'd',
                InputOption::VALUE_NONE,
                'Delete existing databases',
            );
    }

    /**
     * @see parent::showHeader()
     */
    protected function showHeader(): void
    {
        $this->io->title("OX database installation");
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws Exception|InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output          = $output;
        $this->input           = $input;
        $this->io              = new SymfonyStyle($this->input, $this->output);
        $this->question_helper = $this->getHelper('question');
        $this->path            = $this->input->getOption('path');
        $this->config          = $this->input->getOption('config');
        $this->delete          = $this->input->getOption('delete');

        $this->showHeader();

        if (!is_dir($this->path)) {
            throw new InvalidArgumentException("'$this->path' is not a valid directory.");
        }

        $this->requireConfigs();

        if (!$this->askQuestions()) {
            return self::SUCCESS;
        }

        // process
        $this->checkConnexion()
            ->checkDatabase()
            ->createDatabase()
            ->createTables()
            ->updateUsers()
            ->grantPermissionsOnModules();

        $this->io->success('Database successfully created !');

        return self::SUCCESS;
    }

    protected function requireConfigs(): void
    {
        // includes configs (legacy)
        require __DIR__ . '/../../../includes/config_all.php';
    }

    /**
     * @return mixed
     * @throws Exception
     */
    private function askQuestions()
    {
        if ($this->config) {
            $is_from_config = true;
        } else {
            $is_from_config = false;
            if (file_exists($this->path . CMbConfig::CONFIG_FILE)) {
                $is_from_config = $this->ask(
                    new ConfirmationQuestion(
                        'Do you want to use the database parameters saved in the OX configuration file [Y/n] ?',
                        true
                    )
                );
            }
        }

        if ($is_from_config) {
            $this->params['host']     = CAppUI::conf('db std dbhost');
            $this->params['database'] = CAppUI::conf('db std dbname');
            $this->params['user']     = CAppUI::conf('db std dbuser');
            $this->params['password'] = CAppUI::conf('db std dbpass');
        } else {
            $this->params['host']     = $this->ask(new Question('Please enter the host name: ', 'localhost'));
            $this->params['database'] = $this->ask(new Question('Enter the database name: ', 'mediboard'));
            $this->params['user']     = $this->ask(new Question('Enter the user name: ', 'root'));
            $this->params['password'] = $this->ask(new Question('Enter the user password: '), true);
        }

        // password admin
        if ($this->config) {
            $this->params['admin_password'] = $this->params['password'];
            $this->io->note('Setting admin account password as the database password');
        } else {
            $question = new Question('Enter the admin account password: ');
            $question->setValidator(function ($answer) {
                if (!preg_match('/^S*(?=\S{6,})(?=\S*[a-zA-Z])(?=\S*[\d])\S*$/', $answer)) {
                    throw new \RuntimeException(
                        'The password should match min 6 characters alpha & numeric'
                    );
                }

                return $answer;
            });
            $question->setMaxAttempts(3);
            $this->params['admin_password'] = $this->ask($question, true);
        }

        // resume
        $params_resume = [];
        foreach ($this->params as $key => $value) {
            if ($key === 'password' || $key === 'admin_password') {
                $value = str_repeat('*', $value !== null ? strlen($value) : 0);
            }
            $params_resume[] = [$key, $value];
        }

        $this->io->table(['config', 'value'], $params_resume);

        // confirm
        return $this->config || $this->ask(
            new ConfirmationQuestion('Do you confirm this settings, and create the database [Y/n] ?', true)
        );
    }

    /**
     * @param Question $question
     *
     * @param bool     $hidden
     *
     * @return mixed
     */
    private function ask(Question $question, bool $hidden = false)
    {
        if ($hidden) {
            $question->setHidden(true);
        }

        return $this->question_helper->ask($this->input, $this->output, $question);
    }

    /**
     * Create pdo & check connexion
     * @return self
     * @throws Exception
     */
    private function checkConnexion(): self
    {
        try {
            $this->pdo = new CommandLinePDO($this->params['host'], $this->params['user'], $this->params['password']);
        } catch (Exception $e) {
            if ($this->output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
                throw $e;
            }
            throw new LogicException("Unable to connect to mysql:host={$this->params['host']}.");
        }

        return $this;
    }

    /**
     * @return self
     * @throws Exception|LogicException
     */
    private function checkDatabase(): self
    {
        if (!preg_match('/^[A-Z0-9_-]+$/i', $this->params['database'])) {
            throw new LogicException('Invalid database name (A-Z0-9_).');
        }

        if ($this->pdo->isDatabaseExists($this->params['database'])) {
            if ($this->delete) {
                $this->pdo->dropDatabase($this->params['database']);
            } else {
                throw new LogicException("Database {$this->params['database']} already exists.");
            }
        }

        return $this;
    }

    /**
     * @return self
     * @throws Exception|LogicException
     */
    private function createDatabase(): self
    {
        if (!$this->pdo->createDatabase($this->params['database'])) {
            throw new LogicException("Unable to create database {$this->params['database']}.");
        }

        // use database
        $this->pdo = new CommandLinePDO(
            $this->params['host'],
            $this->params['user'],
            $this->params['password'],
            $this->params['database']
        );

        return $this;
    }

    /**
     * @return self
     * @throws LogicException
     */
    private function createTables(): self
    {
        if (!$this->pdo->createTables()) {
            throw new LogicException("Unable to create tables in database {$this->params['database']}.");
        }

        return $this;
    }


    /**
     * @return self
     * @throws LogicException
     */
    private function updateUsers(): self
    {
        $salt     = CUser::createSalt();
        $password = CUser::saltPassword($salt, $this->params['admin_password']);

        if (!$this->pdo->updateUsers($salt, $password)) {
            throw new LogicException('Unable to update user admin.');
        }

        return $this;
    }


    /**
     * @return self
     * @throws LogicException
     */
    private function dropDatabase(): self
    {
        if (!$this->pdo->dropDatabase($this->params['database'])) {
            throw new LogicException("Unable to drop database {$this->params['database']}.");
        }

        return $this;
    }

    /**
     * @return self
     * @throws LogicException
     */
    private function grantPermissionsOnModules(): self
    {
        $this->pdo->query(
            "INSERT INTO `perm_module` (user_id, mod_id, permission, view) " .
            "SELECT  user_id, NULL, 2, 2 FROM `users` where template != '1';"
        );
        if ($this->io->isVerbose()) {
            $this->io->writeln('Permissions set for users on modules');
        }
        return $this;
    }
}
