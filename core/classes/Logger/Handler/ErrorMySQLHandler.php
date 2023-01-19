<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Logger\Handler;

use DateTimeImmutable;
use Exception;
use Monolog\Handler\AbstractProcessingHandler;
use Ox\Core\CApp;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\System\CErrorLog;
use Throwable;

/**
 * Handler that can write errors to MySQL database using CErrorLog object.
 */
class ErrorMySQLHandler extends AbstractProcessingHandler
{
    protected function write(array $record): void
    {
        /** @var DateTimeImmutable $datetime */
        $datetime = $record['datetime'];

        /** @var Throwable $exception */
        $exception = $record['context']['exception'];

        CErrorLog::insert(
            $record['extra']['user_id'],
            $record['extra']['server_ip'],
            $datetime->format('Y-m-d H:i:s.u'),
            $record['extra']['request_uuid'],
            $record['extra']['type'],
            $exception->getMessage(),
            $record['extra']['file'],
            $exception->getLine(),
            $record['extra']["signature_hash"],
            $record['extra']['count'],
            $record['extra']['data']
        );
    }

    /**
     * @throws Exception
     */
    public function handleBatch(array $records): void
    {
        if (CApp::isReadonly()) {
            throw new Exception('App is readonly');
        }

        if (!CSQLDataSource::get('std', true)) {
            throw new Exception('Main datasource is not available');
        }

        parent::handleBatch($records);
    }
}
