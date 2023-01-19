<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use Ox\Core\CMbException;

/**
 * Description
 */
class DataAuditor
{
    /** @var DataAuditTarget */
    private $first_target;

    /** @var DataAuditTarget */
    private $second_target;

    /** @var string */
    private $start_date;

    /** @var string */
    private $end_date;

    /** @var SchemaDiff|null */
    private $schema_diff;

    /** @var LogDiff|null */
    private $log_diff;

    /**
     * DataAuditor constructor.
     *
     * @param string $host1
     * @param string $host2
     * @param string $start_date
     * @param string $end_date
     *
     * @throws CMbException
     */
    public function __construct(string $host1, string $host2, string $start_date, string $end_date)
    {
        if (!$host1 || !$host2 || !$start_date || !$end_date) {
            throw new CMbException('common-error-Missing parameter');
        }

        $this->first_target  = new DataAuditTarget($host1);
        $this->second_target = new DataAuditTarget($host2);

        $this->start_date = $start_date;
        $this->end_date   = $end_date;
    }

    /**
     * Run the audit
     *
     * @return void
     * @throws CMbException
     */
    public function run()
    {
        $this->computeSchemaDiff();
        $this->computeLogDiff();
    }

    /**
     * @return DataAuditTarget
     */
    public function getFirstTarget(): DataAuditTarget
    {
        return $this->first_target;
    }

    /**
     * @return DataAuditTarget
     */
    public function getSecondTarget(): DataAuditTarget
    {
        return $this->second_target;
    }

    /**
     * @return SchemaDiff|null
     */
    public function getSchemaDiff(): ?SchemaDiff
    {
        return $this->schema_diff;
    }

    /**
     * @return LogDiff|null
     */
    public function getLogDiff(): ?LogDiff
    {
        return $this->log_diff;
    }

    /**
     * Compute the schema diff.
     *
     * @return void
     * @throws CMbException
     */
    private function computeSchemaDiff()
    {
        $this->schema_diff = new SchemaDiff($this->first_target->parseSchema(), $this->second_target->parseSchema());
    }

    /**
     * Compute the log diff.
     *
     * @return void
     * @throws CMbException
     */
    private function computeLogDiff()
    {
        $this->log_diff = new LogDiff(
            $this->first_target->parseLogs($this->start_date, $this->end_date),
            $this->second_target->parseLogs($this->start_date, $this->end_date)
        );
    }
}
