<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Logger\Formatter;

use Monolog\Formatter\FormatterInterface;
use Ox\Core\Logger\LoggableElasticObjectInterface;

class ElasticObjectFormatter implements FormatterInterface
{
    private LoggableElasticObjectInterface $elastic_object;

    public function __construct(LoggableElasticObjectInterface $elastic_object)
    {
        $this->elastic_object = $elastic_object;
    }

    /**
     * @param array $record
     *
     * @return LoggableElasticObjectInterface
     */
    public function format(array $record): LoggableElasticObjectInterface
    {
        return $this->elastic_object->buildFromLogRecord($record);
    }

    /**
     * @param array $records
     *
     * @return LoggableElasticObjectInterface[]
     */
    public function formatBatch(array $records): array
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }

    /**
     * @return LoggableElasticObjectInterface
     */
    public function getElasticObject(): LoggableElasticObjectInterface
    {
        return $this->elastic_object;
    }
}
