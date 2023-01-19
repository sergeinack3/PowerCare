<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Logger\Wrapper;

use Monolog\Handler\FallbackGroupHandler;
use Monolog\Handler\GroupHandler;
use Monolog\Logger;
use Ox\Core\Logger\Handler\ErrorBufferHandler;
use Ox\Core\Logger\Handler\ErrorCronLogHandler;
use Ox\Core\Logger\Handler\ErrorElasticHandler;
use Ox\Core\Logger\Handler\ErrorLastChanceHandler;
use Ox\Core\Logger\Handler\ErrorMySQLHandler;
use Ox\Core\Logger\Processor\ErrorProcessor;

/**
 * Wrapper around Monolog/Logger to log Error log in stream (file)
 */
class ErrorLoggerWrapper
{
    public const CHANNEL = 'error';

    private Logger $logger;

    public function __construct()
    {
        $processor = new ErrorProcessor();

        $mysql       = new ErrorMySQLHandler();
        $elastic     = new ErrorElasticHandler();
        $last_chance = new ErrorLastChanceHandler();

        $fallback_group = new FallbackGroupHandler([$elastic, $mysql, $last_chance]);

        $buffer = new ErrorBufferHandler($fallback_group);

        $this->logger = new Logger(self::CHANNEL, [$buffer], [$processor]);
    }


    public function log(int $level, string $message, array $context): bool
    {
        $this->logger->log($level, $message, $context);

        return true;
    }
}
