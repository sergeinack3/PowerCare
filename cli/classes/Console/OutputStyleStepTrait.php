<?php

/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Console;

use LogicException;
use Symfony\Component\Console\Output\OutputInterface;

trait OutputStyleStepTrait
{
    private OutputInterface $step_output;

    private int $step_per_line;

    private int $total;

    private int $count_echo;

    private int $current_line;

    /**
     * @param OutputInterface $output        Output interface
     * @param int             $total         Total of element in array of data
     * @param int             $step_per_line How many dots per line
     *
     * @return void
     */
    private function initStep(OutputInterface $output, int $total, int $step_per_line = 50): void
    {
        $this->step_output   = $output;
        $this->total         = $total;
        $this->step_per_line = $step_per_line;
        $this->count_echo    = 0;
        $this->current_line  = 0;
    }

    private function step(string $msg): void
    {
        if ($this->total < 1) {
            throw new LogicException('Total count of data need must be greater than 0');
        }

        $this->count_echo++;

        if ($this->current_line <= $this->step_per_line) {
            $this->current_line++;
            $this->step_output->write($msg);
        }

        if (($this->current_line === $this->step_per_line) || ($this->count_echo === $this->total)) {
            if ($this->current_line < $this->step_per_line) {
                $this->step_output->write(str_repeat(' ', $this->step_per_line - $this->current_line));
            }

            $this->current_line = 0;
            $percent            = round(($this->count_echo * 100) / $this->total);

            $this->step_output->writeln(
                sprintf(" %s / %d (%d%%)", str_pad($this->count_echo, 4), $this->total, str_pad($percent, 2))
            );
        }
    }
}
