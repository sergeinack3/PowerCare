<?php

/**
 * @package Mediboard\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Tests\Extension;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CSQLDataSource;
use Ox\Core\Logger\LoggerLevels;
use PHPUnit\Runner\AfterLastTestHook;
use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeFirstTestHook;
use Symfony\Component\Filesystem\Filesystem;


final class TestsCountQueriesExtension implements BeforeFirstTestHook, AfterTestHook, AfterLastTestHook
{

    protected string $outputFile;

    protected array $data = [];

    public function __construct(string $outputFile = 'tmp/phpunit-count-queries.json')
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
        CSQLDataSource::$log         = true;
        CSQLDataSource::$log_entries = [];
    }

    /**
     * @param string $test
     * @param float  $time
     *
     * @return void
     */
    public function executeAfterTest(string $test, float $time): void
    {
        $this->data[]                = [
            'test'  => $test,
            'count' => count(CSQLDataSource::$log_entries),
        ];
        CSQLDataSource::$log_entries = [];
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
     * Orders data array elements by count in descending order
     *
     * @return void
     */
    private function sortData(): void
    {
        $order_data = array_map(
            function ($element) {
                return $element['count'];
            },
            $this->data
        );

        array_multisort($order_data, SORT_DESC, $this->data);
    }
}
