<?php

/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

class CTableIntegrity
{
    /** @var string */
    private $dsn;

    /** @var string */
    private $module_name;

    /** @var string */
    private $class_name;

    /** @var string */
    private $table_name;

    /** @var bool */
    private $table_exists = false;

    /** @var int */
    private $row_count = 0;

    public function __construct(
        ?string $class_name = null,
        ?string $table_name = null,
        ?string $module_name = null,
        ?string $dsn = null
    ) {
        $this->class_name  = $class_name;
        $this->table_name  = $table_name;
        $this->module_name = $module_name;
        $this->dsn         = $dsn;
    }

    /**
     * @return string
     */
    public function getModuleName(): ?string
    {
        return $this->module_name;
    }

    /**
     * @param string $module_name
     */
    public function setModuleName(?string $module_name): void
    {
        $this->module_name = $module_name;
    }

    /**
     * @return string
     */
    public function getClassName(): ?string
    {
        return $this->class_name;
    }

    /**
     * @param string $class_name
     */
    public function setClassName(?string $class_name): void
    {
        $this->class_name = $class_name;
    }

    /**
     * @return string
     */
    public function getTableName(): ?string
    {
        return $this->table_name;
    }

    /**
     * @param string $table_name
     */
    public function setTableName(?string $table_name): void
    {
        $this->table_name = $table_name;
    }

    /**
     * @return bool
     */
    public function getTableExists(): bool
    {
        return $this->table_exists;
    }

    /**
     * @param bool $table_exists
     */
    public function setTableExists(bool $table_exists): void
    {
        $this->table_exists = $table_exists;
    }

    /**
     * @return int
     */
    public function getRowCount(): string
    {
        return number_format($this->row_count, 0, ',', ' ');
    }

    /**
     * @param int $row_count
     */
    public function setRowCount(int $row_count): void
    {
        $this->row_count = $row_count;
    }

    /**
     * @return string
     */
    public function getDsn(): ?string
    {
        return $this->dsn;
    }

    /**
     * @param string $dsn
     */
    public function setDsn(?string $dsn): void
    {
        $this->dsn = $dsn;
    }
}
