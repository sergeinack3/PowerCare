<?php

/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Console;

use Exception;
use Ox\Cli\CommandLinePDO;
use Ox\Cli\MediboardCommand;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbConfig;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\Database\SetupUpdater;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * Class DBCompare
 *
 * @package Ox\Cli\Console
 */
class DBCompare extends MediboardCommand implements IAppDependantCommand
{
    /** @var OutputInterface */
    protected $output;

    /** @var InputInterface */
    protected $input;

    /** @var SymfonyStyle */
    protected $io;

    /**
     * @see parent::configure()
     */
    protected function configure(): void
    {
        $this
            ->setName('ox-db:compare')
            ->setDescription('Compare database schema against model');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output    = $output;
        $this->input     = $input;
        $this->io        = new SymfonyStyle($this->input, $this->output);

        return $this->checkDatabaseAgainstModel();
    }

    /**
     * Function that compares database against object properties
     *
     * @throws Exception
     */
    private function checkDatabaseAgainstModel(): int
    {
        $this->io->section('Checking database schema against model...');

        $no_ds = [];
        $missing_tables = [];
        $missing_fields = [];
        $missing_specs = [];

        $classes = CClassMap::getInstance()->getClassChildren(CStoredObject::class, false, true);

        /** @var CStoredObject $instance */
        foreach ($classes as $class) {
            try {
                $instance = new $class();
            } catch (Throwable $e) {
                continue;
            }

            if (!$instance->_spec) {
                $missing_specs[] = get_class($instance);
                continue;
            }

            if ($instance->_spec->table) {
                /** @var CSQLDataSource $ds */
                if (!($ds = $instance->getDS())) {
                    $no_ds[] = $instance->_class;
                    continue;
                }

                if (!$ds->hasTable($instance->_spec->table)) {
                    $missing_tables[] = $instance->_spec->table;
                } else {
                    foreach ($instance->getPlainFields() as $field_name => $prop) {
                        if (!$ds->hasField($instance->_spec->table, $field_name)) {
                            $missing_fields[$instance->_spec->table][] = $field_name;
                        }
                    }
                }
            }
        }

        if (
            !empty($no_ds)
            || !empty($missing_tables)
            || !empty($missing_fields)
            || !empty($missing_specs)
        ) {
            if (!empty($no_ds)) {
                $this->io->warning('No datasource was found for ' . count($no_ds) . 'classes');
                $this->io->listing($no_ds);
            }
            if (!empty($missing_tables)) {
                $this->io->warning('Missing tables (' . count($missing_tables) . ')');
                $this->io->listing($missing_tables);
            }
            if (!empty($missing_fields)) {
                $this->io->warning('Missing fields in ' . count($missing_fields) . ' tables');
                $table = $this->io->createTable()
                    ->setHeaderTitle('Missing fields')
                    ->setHeaders(['Table', 'Missing fields']);
                foreach ($missing_fields as $name => $fields) {
                    $table->addRow([$name, implode(PHP_EOL, $fields)]);
                }
                $table->render();
            }
            if (!empty($missing_specs)) {
                $this->io->warning('Missing object specs');
                $this->io->listing($missing_specs);
            }
            $this->io->error('Database does not match data model !');
            return self::FAILURE;
        } else {
            $this->io->success('Database matches data model');
            return self::SUCCESS;
        }
    }
}
