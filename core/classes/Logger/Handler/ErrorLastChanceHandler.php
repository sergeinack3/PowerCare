<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Logger\Handler;

use Monolog\Handler\Handler;
use Ox\Core\CApp;
use Ox\Core\Logger\LoggerLevels;
use Throwable;

/**
 * Last chance to know what happened
 */
class ErrorLastChanceHandler extends Handler
{
    public function handleBatch(array $records): void
    {
        CApp::log(
            sprintf('There is %d non handled errors', count($records)),
            null,
            LoggerLevels::LEVEL_CRITICAL
        );
    }

    public function isHandling(array $record): bool
    {
        return true;
    }

    public function handle(array $record): bool
    {
        return false;
    }
}
