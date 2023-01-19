<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Logger\Handler;

use Exception;
use InvalidArgumentException;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Ox\Core\Elastic\ElasticObjectManager;
use Ox\Core\Logger\Formatter\ElasticObjectFormatter;
use RuntimeException;
use Throwable;

abstract class AbstractElasticObjectHandler extends AbstractProcessingHandler
{
    private bool    $ignore_errors;
    protected ?bool $is_active = null;

    /**
     * @param bool $ignore_errors
     * @param int  $level
     * @param bool $bubble
     */
    public function __construct(
        bool $ignore_errors = false,
        int $level = Logger::DEBUG,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);
        $this->ignore_errors = $ignore_errors;
    }

    protected function write(array $record): void
    {
        $this->bulkSend([$record['formatted']]);
    }

    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        if ($formatter instanceof ElasticObjectFormatter) {
            return parent::setFormatter($formatter);
        }

        throw new InvalidArgumentException(
            'ElasticsearchHandler is only compatible with ElasticObjectFormatter'
        );
    }

    /**
     * @param array $records
     *
     * @return void
     * @throws Exception
     */
    public function handleBatch(array $records): void
    {
        if (!$this->canHandle()) {
            throw new Exception("Cannot handle records");
        }
        if ($records === []) {
            return;
        }

        $documents = $this->getFormatter()->formatBatch($records);
        $this->bulkSend($documents);
    }

    /**
     * Check if records can be handled
     *
     * @return bool
     */
    abstract protected function canHandle(): bool;

    /**
     * @param array $records
     *
     * @return void
     * @throws RuntimeException
     */
    protected function bulkSend(array $records): void
    {
        try {
            ElasticObjectManager::getInstance()->store($records);
        } catch (Throwable $e) {
            if (!$this->ignore_errors) {
                throw new RuntimeException('Error sending messages to Elasticsearch', 0, $e);
            }
        }
    }
}
