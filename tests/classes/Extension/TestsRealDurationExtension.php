<?php

/**
 * @package Mediboard\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Tests\Extension;

use Exception;
use PHPUnit\Runner\AfterLastTestHook;
use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeFirstTestHook;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class TestsRealDurationExtension
 * @package Ox\Tests\Extension
 */
final class TestsRealDurationExtension implements BeforeFirstTestHook, AfterTestHook, AfterLastTestHook
{
    /** @var string */
    protected $outputFile;

    /** @var array  */
    protected $data = [];

    /** @var float */
    protected $time;

    public function __construct(string $outputFile = 'tmp/phpunit-real-duration.json')
    {
        $this->outputFile = $outputFile;
    }

    /**
     * Sets starting time
     *
     * @return void
     */
    public function executeBeforeFirstTest(): void
    {
        $this->time = microtime(true);
    }

    /**
     * @param string $test
     * @param float  $time
     *
     * @return void
     */
    public function executeAfterTest(string $test, float $time): void
    {
        $time = microtime(true);
        $duration = round($time - $this->time, 3);
        $this->data[] = [
            'test'     => $test,
            'duration' => $duration,
        ];
        $this->time = $time;
    }

    /**
     * Write to json output file
     *
     * @return void
     * @throws Exception
     */
    public function executeAfterLastTest(): void
    {
        $this->sortData();

        $jsonContent = json_encode($this->data, JSON_INVALID_UTF8_IGNORE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(json_last_error_msg());
        }

        if (!empty($this->outputFile)) {
            $fs = new Filesystem();
            if ($fs->exists($this->outputFile)) {
                $fs->remove($this->outputFile);
            }
            $fs->dumpFile($this->outputFile, $jsonContent);
        }
    }

    /**
     * Orders data array elements by duration in descending order
     *
     * @return void
     */
    private function sortData(): void
    {
        $duration_order_data = array_map(
            function ($element) {
                return $element['duration'];
            },
            $this->data
        );

        array_multisort($duration_order_data, SORT_DESC, $this->data);
    }
}
