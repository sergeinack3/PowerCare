<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Console\Fixtures;

use Exception;
use Ox\Cli\Console\IAppDependantCommand;
use Ox\Cli\MediboardCommand;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\GroupFixturesInterface;
use ReflectionException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FixturesDebug extends MediboardCommand implements IAppDependantCommand
{
    use FixturesTrait;

    private array $groups = [];

    private array $fixtures = [];

    private string $namespace = "";

    /**
     * @see parent::configure()
     */
    protected function configure(): void
    {
        $this
            ->setName('ox-fixtures:debug')
            ->setDescription('List all fixtures')
            ->addOption(
                'groups',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Filter by group name'
            )
            ->addOption(
                'namespace',
                null,
                InputOption::VALUE_REQUIRED,
                'If you only want to execute fixtures of a specific namespace (WILDCARD)',
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
        $this->output    = $output;
        $this->input     = $input;
        $this->io        = new SymfonyStyle($this->input, $this->output);
        $this->path      = dirname(__DIR__, 4);
        $this->groups    = (array)$input->getOption('groups');
        $this->namespace = (string)$input->getOption('namespace');

        $this->findFixtures()
            ->showFixtures();

        return self::SUCCESS;
    }

    /**
     * @throws ReflectionException
     */
    private function findFixtures(): FixturesDebug
    {
        $this->fixtures = (new FixturesFinder($this->path, $this->groups, $this->namespace))->find();

        return $this;
    }

    private function showFixtures(): FixturesDebug
    {
        $table       = new Table($this->output);
        $table_style = new TableStyle();
        $table_style->setHeaderTitleFormat('<info>%s</info>');

        $rows = [];
        /** @var Fixtures $instance */
        foreach ($this->fixtures as $instance) {
            $class_name = get_class($instance);
            $group      = is_subclass_of($instance, GroupFixturesInterface::class) ? $instance->getGroup() : [];
            $rows[]     = [
                $class_name,
                $instance->getDescription(),
                $group[0] ?? null,
                $group[1] ?? null,
                $instance->getLastChange(),
            ];
        }

        $table
            ->setStyle($table_style)
            ->setHeaderTitle(count($rows) . " Fixtures")
            ->setHeaders(['Class', 'Description', 'Group', 'Priority', 'Last change'])
            ->setRows($rows)
            ->render();

        return $this;
    }
}
