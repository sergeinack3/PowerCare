<?php

/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Console;

use Exception;
use Ox\Cli\MediboardCommand;
use PDO;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DBSchemaDiff
 */
class DBSchemaDiff extends MediboardCommand
{
    use OutputStyleStepTrait;

    /** @var int */
    private const NB_STEP_PER_LINE = 50;

    /** @var OutputInterface */
    protected $input;

    /** @var OutputInterface */
    protected $output;

    /** @var string */
    private $verbosity;

    /** @var array */
    private $datas = [];

    /** @var array */
    private $missing_table = [];

    /** @var array */
    private $diff_table = [];

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName("db:schemadiff")
            ->setDescription("Schema Diff analysis between two mysql host")
            ->setHelp("This help");

        foreach (range(1, 2) as $index) {
            $this
                ->addArgument(
                    "host$index",
                    InputArgument::REQUIRED,
                    "MySQL hostname #$index"
                )->addArgument(
                    "schema$index",
                    InputArgument::REQUIRED,
                    "MySQL schema #$index"
                )
                ->addArgument(
                    "user$index",
                    InputArgument::REQUIRED,
                    "MySQL username #$index"
                )
                ->addArgument(
                    "pass$index",
                    InputArgument::REQUIRED,
                    "MySQL password #$index"
                );
        }
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
        $this->input     = $input;
        $this->output    = $output;
        $this->verbosity = $output->getVerbosity();

        /**
         * Get datas
         */
        foreach (range(1, 2) as $index) {
            $host   = $this->input->getArgument("host$index");
            $schema = $this->input->getArgument("schema$index");
            $user   = $this->input->getArgument("user$index");
            $pass   = $this->input->getArgument("pass$index");

            // Connect host
            $dsn = "mysql:dbname=information_schema;host=" . $host;

            try {
                $pdo = new PDO($dsn, $user, $pass === '' ? null : $pass);
            } catch (Exception $e) {
                $this->out($this->output, "<error>Failed to connect to host $index\n" . $e->getMessage() . " </error>");
                throw $e;
            }

            $this->out(
                $this->output,
                "<info>Successfully connected to host $index </info> "
            );

            $query     = $this->getQuery();
            $statement = $pdo->prepare($query);
            $statement->bindValue(':schema', $schema, PDO::PARAM_STR);

            if (!$statement->execute()) {
                $tmp = $statement->errorInfo();
                throw new Exception($tmp[2]);
            }

            $this->out(
                $this->output,
                "<info>Successfully retrieved table schema {$schema} info for host $index </info>"
            );
            $tables = $statement->fetchAll(PDO::FETCH_OBJ);

            foreach ($tables as $table) {
                $this->datas[$table->table_name][$index] = [
                    'rows'         => (int)$table->table_rows,
                    'data_length'  => (int)$table->data_length,
                    'index_length' => (int)$table->index_length,
                    'columns'      => (int)$table->columns,
                ];
            }
        }

        if (empty($this->datas)) {
            $this->out(
                $this->output,
                "<error>No table found</error>"
            );
        }

        $this->initStep($this->output, count($this->datas), self::NB_STEP_PER_LINE);

        /**
         * Step
         */
        foreach ($this->datas as $table => $host) {
            $is_missing_table = false;
            $is_diff_table    = false;

            // Missing tables ?
            if (!isset($host[1])) {
                $this->missing_table[] = "Missing table {$table} in host 1";
                $is_missing_table      = true;
            }
            if (!isset($host[2])) {
                $this->missing_table[] = "Missing table {$table} in host 2";
                $is_missing_table      = true;
            }
            if ($is_missing_table) {
                $this->step('M');
                continue;
            }

            // Check tables ?
            foreach ($host[1] as $key => $value) {
                if ($value !== $host[2][$key]) {
                    $this->diff_table[] = $table;
                    $is_diff_table      = true;
                    break;
                }
            }

            // Step
            $is_diff_table ? $this->step('D') : $this->step('.');
        }


        /**
         * Report
         */
        if (!empty($this->missing_table)) {
            $this->output->writeln('<error>There was ' . count($this->missing_table) . ' missing tables :</error>');

            foreach ($this->missing_table as $msg) {
                $this->output->writeln(' - ' . $msg);
            }
            $this->output->writeln('');
        }

        if (!empty($this->diff_table)) {
            $this->output->writeln(
                '<error>There was ' . count($this->diff_table) . ' tables with differences:</error>'
            );
            $tableStyle = new TableStyle();
            $tableStyle->setCellHeaderFormat('<b>%s</b>')->setCellRowFormat('%s');
            $tableStyle->setHeaderTitleFormat('<error>%s</error>');

            foreach ($this->diff_table as $table_name) {
                $this->output->writeln(' - table ' . $table_name);
            }
            $this->output->writeln('');

            if ($this->verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
                foreach ($this->diff_table as $table_name) {
                    $table = new Table($this->output);
                    $table->setStyle($tableStyle);
                    $table->setHeaderTitle($table_name);
                    $table->setHeaders(
                        [
                            'Host',
                            'Rows',
                            'Data Length',
                            'Index Length',
                            'Columns',
                        ]
                    );

                    $rows = [];
                    foreach (range(1, 2) as $host) {
                        $row = array_values($this->datas[$table_name][$host]);
                        array_unshift($row, 'Host ' . $host);
                        $rows[] = $row;
                    }
                    $table->addRows($rows);
                    $table->render();
                    $this->output->writeln('');
                }
            }
        }


        /**
         * Return
         */
        if (empty($this->missing_table) && empty($this->diff_table)) {
            return self::SUCCESS;
        }

        return self::FAILURE;
    }

    private function getQuery(): string
    {
        return 'SELECT tables.table_name, tables.table_rows, tables.data_length, tables.index_length, COUNT(columns.column_name) as columns
                FROM tables, columns
                WHERE tables.table_schema = :schema
                AND columns.table_schema = tables.table_schema
                AND columns.TABLE_NAME = tables.table_name
                GROUP BY columns.TABLE_NAME';
    }
}
