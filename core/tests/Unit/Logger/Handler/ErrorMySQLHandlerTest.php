<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Logger\Handler;

use DateTimeImmutable;
use Exception;
use Ox\Core\CApp;
use Ox\Core\CSQLDataSource;
use Ox\Core\Logger\Handler\ErrorMySQLHandler;
use Ox\Core\Logger\LoggerLevels;
use Ox\Mediboard\System\CErrorLog;
use Ox\Tests\OxUnitTestCase;

class ErrorMySQLHandlerTest extends OxUnitTestCase
{
    public function testHandleBatchReadonlyThrowException(): void
    {
        try {
            CApp::$readonly = true;

            $this->expectExceptionObject(new Exception('App is readonly'));
            $handler = new ErrorMySQLHandler();
            $handler->handleBatch([]);
        } finally {
            CApp::$readonly = false;
        }
    }

    public function testHandleBatchNoDatasourceThrowException(): void
    {
        try {
            $ds                                 = CSQLDataSource::$dataSources['std'];
            CSQLDataSource::$dataSources['std'] = null;

            $this->expectExceptionObject(new Exception('Main datasource is not available'));

            $handler = new ErrorMySQLHandler();
            $handler->handleBatch([]);
        } finally {
            CSQLDataSource::$dataSources['std'] = $ds;
        }
    }

    public function testWrite(): void
    {
        $error_log          = new CErrorLog();
        $inital_error_count = $error_log->countList();

        $handler = new ErrorMySQLHandler();
        $handler->handleBatch(
            [
                [
                    'level'    => LoggerLevels::LEVEL_WARNING,
                    'datetime' => new DateTimeImmutable(),
                    'context'  => [
                        'exception' => new Exception('test'),
                    ],
                    'extra'    => [
                        'user_id'        => 14,
                        'server_ip'      => null,
                        'request_uuid'   => uniqid(),
                        'type'           => 'exception',
                        'file'           => null,
                        'signature_hash' => uniqid(),
                        'count'          => 1,
                        'data'           => [
                            'stacktrace'   => [],
                            'param_GET'    => [],
                            'param_POST'   => [],
                            'session_data' => [],
                        ],
                    ],
                ],
            ]
        );

        $new_error_count = $error_log->countList();

        $this->assertGreaterThan($inital_error_count, $new_error_count);
    }
}
