<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Logger\Wrapper;

use Monolog\Handler\BufferHandler;
use Monolog\Handler\FallbackGroupHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Ox\Core\Logger\Formatter\ApplicationLineFormatter;
use Ox\Core\Logger\Handler\ApplicationCronLogHandler;
use Ox\Core\Logger\Handler\ApplicationElasticHandler;
use Ox\Core\Logger\LoggerLevels;
use Ox\Core\Logger\Processor\ApplicationProcessor;

/**
 * Wrapper around Monolog/Logger to log Application log in stream (file)
 */
class ApplicationLoggerWrapper
{
    public const CHANNEL      = 'app';
    public const FILE_PATH    = DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'application.log';
    public const BUFFER_LIMIT = 100;

    private Logger $logger;

    public function __construct()
    {
        $processor = new ApplicationProcessor();
        $elastic   = new ApplicationElasticHandler();
        $stream    = new StreamHandler(self::getPathApplicationLog());
        $stream->setFormatter(new ApplicationLineFormatter());

        $group        = new FallbackGroupHandler([$elastic, $stream], false);
        $buffer       = new BufferHandler($group, self::BUFFER_LIMIT, LoggerLevels::LEVEL_DEBUG, true, true);
        $this->logger = new Logger(self::CHANNEL, [$buffer], [$processor]);
    }

    public function log(int $level, string $message, array $context): bool
    {
        $this->logger->log($level, $message, $context);

        return true;
    }

    public static function getPathApplicationLog(): string
    {
        return dirname(__DIR__, 4) . self::FILE_PATH;
    }
}
