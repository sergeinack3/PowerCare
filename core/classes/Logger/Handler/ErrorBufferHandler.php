<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Logger\Handler;

use Error;
use Exception;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\FallbackGroupHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\Kernel\Exception\HttpException;
use Ox\Mediboard\System\CErrorLogWhiteList;
use Symfony\Component\ErrorHandler\ErrorHandler;

/**
 * Buffer that handle errors depending on their signatures.
 *
 * @see dev/ADR/062-error-logging.md
 *
 * @see BufferHandler
 * @see FallbackGroupHandler
 */
class ErrorBufferHandler extends BufferHandler
{
    public const TOTAL_LIMIT  = 100_000;
    public const BUFFER_LIMIT = 1_000;

    public const SAME_HASH_COUNT_LIMIT     = 10_000;
    public const DISTINCT_HASH_COUNT_LIMIT = 1_000;

    private static int $error_count = 0;

    /** @var array [hash => (int) buffer_count ] */
    private array $buffered_signatures = [];

    /** @var array  [hash => (int) total_count ] */
    private array $total_buffered_signatures = [];

    /** @var array [hash => (int) whitelisted_count ] */
    private array $error_whitelist = [];

    private bool $is_limit_reached = false;

    /**
     * @param int|string $level
     */
    public function __construct(
        HandlerInterface $handler,
        int $bufferLimit = self::BUFFER_LIMIT,
        $level = Logger::INFO,
        bool $bubble = true,
        bool $flushOnOverflow = false
    ) {
        parent::__construct($handler, $bufferLimit, $level, $bubble, $flushOnOverflow);
    }

    public function handle(array $record): bool
    {
        // Ignore not loggable exception
        if (!$this->canLog($record)) {
            return false;
        }

        static::$error_count++;

        // Stop logging when limits reached
        if ($this->is_limit_reached || static::$error_count > $this->getTotalLimit()) {
            return false;
        }

        if (!$this->initialized) {
            if (Cache::isInitialized()) {
                $this->initWhiteList();
            }

            // Previously in SoA
            ini_set("log_errors_max_len", "4M");

            // __destructor() doesn't get called on Fatal errors
            CApp::registerShutdown([$this, 'close'], CApp::ERROR_PRIORITY);
            $this->initialized = true;
        }

        if ($this->processors) {
            $record = $this->processRecord($record);
        }

        $signature = $record['extra']['signature_hash'];

        if ($this->isWhiteListed($signature)) {
            return true;
        }

        // Increase total buffered signatures
        if (!array_key_exists($signature, $this->total_buffered_signatures)) {
            $this->total_buffered_signatures[$signature] = 0;
        }

        $this->total_buffered_signatures[$signature]++;

        // Limit distinct hash per hit
        if (count($this->total_buffered_signatures) >= $this->getDistinctHashCountLimit()) {
            $this->is_limit_reached = true;
        }

        // Limit same hash per hit
        if ($this->total_buffered_signatures[$signature] >= $this->getSameHashCountLimit()) {
            $this->is_limit_reached = true;
        }

        // Increase current buffered signatures
        if (!array_key_exists($signature, $this->buffered_signatures)) {
            $this->buffered_signatures[$signature] = 0;

            $this->buffer[$signature] = $record;
        }

        $this->buffered_signatures[$signature]++;
        $this->bufferSize++;

        if ($this->bufferLimit > 0 && $this->bufferSize === $this->bufferLimit) {
            $this->flush();
        }

        return false === $this->bubble;
    }

    /**
     * Flush the buffer and send the records to $this->handler using batch mode.
     * Before sending the buffer add the count to each record.
     */
    public function flush(): void
    {
        foreach ($this->buffer as $hash => $record) {
            $this->buffer[$hash]['extra']['count'] = $this->buffered_signatures[$hash];
        }

        parent::flush();
    }

    /**
     * Reset the buffer, bufferSize and buffered_signatures.
     */
    public function clear(): void
    {
        parent::clear();

        $this->buffered_signatures = [];
    }

    /**
     * Flush the buffer and close the handler.
     * After closing handle the whitelisted error list to increase the number of hits.
     *
     * @throws Exception
     */
    public function close(): void
    {
        parent::close();

        if (Cache::isInitialized()) {
            // If whitelist grow with multiple different hash we should optimize this to reduce the number of queries.
            foreach ($this->error_whitelist as $hash => $count) {
                if ($count > 0) {
                    $wl       = new CErrorLogWhiteList();
                    $wl->hash = $hash;
                    $wl->loadMatchingObjectEsc();
                    $wl->count += $count;
                    $wl->store();
                }
            }
        }
    }

    public static function getErrorCount(): int
    {
        return static::$error_count;
    }

    /**
     * Load the multiple hashes and put them as keys in an array.
     *
     * @throws Exception
     */
    private function initWhiteList(): void
    {
        // Get list of hashes in DB
        $whiteList = new CErrorLogWhiteList();
        if ($whiteList->getDS()->hasTable($whiteList->_spec->table, false)) {
            $hashes = $whiteList->loadColumn('hash');
            if (is_array($hashes)) {
                $this->error_whitelist = array_fill_keys($hashes, 0);
            }
        }
    }

    /**
     * Tell wether a hash exists in the whitelist or not.
     * If the hash exists add 1 to its count.
     */
    private function isWhiteListed(string $error_hash): bool
    {
        // Increment the whitelist counter for $error_hash
        if (array_key_exists($error_hash, $this->error_whitelist)) {
            $this->error_whitelist[$error_hash]++;

            return true;
        }

        return false;
    }

    /**
     * A record must be at least of level info and contains an exception context.
     *
     * For the php channel if the exception context is an Error do not log it yet (it will come back through the
     * ErrorListener).
     * ErrorHandler cast errors to ErrorException in ErrorHandler::handleError
     *
     * A throwable can be logged if either :
     * - it is not a HttpException
     * - it is a HttpException with isLoggable === true
     *
     * @see ErrorHandler::handleError
     */
    private function canLog(array $record): bool
    {
        // Log level must be high enough and the context must contain an exception.
        if ($record['level'] < $this->level || !isset($record['context']['exception'])) {
            return false;
        }

        $throwable = $record['context']['exception'];

        // Error objects on the php channel will come back through the ErrorListener.
        if ($record['channel'] === 'php' && $throwable instanceof Error) {
            return false;
        }

        if ($throwable instanceof HttpException && !$throwable->isLoggable()) {
            return false;
        }

        return true;
    }

    protected function getTotalLimit(): int
    {
        return self::TOTAL_LIMIT;
    }

    protected function getSameHashCountLimit(): int
    {
        return self::SAME_HASH_COUNT_LIMIT;
    }

    protected function getDistinctHashCountLimit(): int
    {
        return self::DISTINCT_HASH_COUNT_LIMIT;
    }
}
