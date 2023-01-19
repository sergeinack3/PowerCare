<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Adapter;

use Generator;
use Ox\Core\CSQLDataSource;
use Ox\Import\Framework\Configuration\ConfigurableInterface;
use Ox\Import\Framework\Configuration\ConfigurationTrait;

/**
 * TODO Make SQL injection proof
 */
class MySqlAdapter implements AdapterInterface, ConfigurableInterface
{
    use ConfigurationTrait;

    /** @var CSQLDataSource */
    private $ds;

    /** @var array */
    private $ljoin = [];

    /**
     * MySqlImportAdapter constructor.
     *
     * @param CSQLDataSource $ds
     */
    public function __construct(CSQLDataSource $ds)
    {
        $this->ds = $ds;
    }

    /**
     * @inheritDoc
     */
    public function retrieve(
        string $collection,
        string $identifier,
        $id,
        array $conditions = [],
        array $select = [],
        array $group = []
    ): ?array {
        $conditions["{$collection}.{$identifier}"] = "= '{$id}'";

        $select = $this->applySelect($select);
        $query  = "SELECT {$select} FROM `{$collection}`";

        $query .= $this->applyLJoin();

        if ($conditions) {
            $query .= $this->applyConditions($conditions);
        }

        if ($group) {
            $query .= $this->applyGroup($group);
        }

        if ($hash = $this->ds->loadHash($query)) {
            return $hash;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function get(
        string $collection,
        int $count = 1,
        int $offset = 0,
        ?array $conditions = [],
        ?array $select = [],
        ?array $group = []
    ): ?Generator {
        $select = $this->applySelect($select);
        $query  = "SELECT {$select} FROM `{$collection}` ";

        $query .= $this->applyLJoin();

        if ($conditions) {
            $query .= $this->applyConditions($conditions);
        }

        if ($group) {
            $query .= $this->applyGroup($group);
        }

        $query .= " LIMIT {$offset},{$count}";

        // Todo: Make ORDER BY (use MapperMetadata for that?)

        $hashes = $this->ds->loadList($query);

        if (!$hashes) {
            return null;
        }

        foreach ($hashes as $_hash) {
            yield $_hash;
        }
    }

    public function count(string $collection, ?array $conditions = [], ?array $group = []): int
    {
        $query = "SELECT COUNT(*) FROM {$collection}";

        $query .= $this->applyLJoin();

        if ($conditions) {
            $query .= $this->applyConditions($conditions);
        }

        if ($group) {
            $query .= $this->applyGroup($group);
        }

        $count = ($group) ? count($this->ds->loadList($query) ?: []) : $this->ds->loadResult($query);

        return $count ?: 0;
    }

    protected function applyConditions(array $conditions): string
    {
        $where = [];

        foreach ($conditions as $_field => $_condition) {
            $field_parts = explode('.', $_field);
            $_field      = isset($field_parts[1]) ? "`{$field_parts[0]}`.{$field_parts[1]}" : "`$_field`";

            $where[] = "({$_field} {$_condition})";
        }

        return ' WHERE ' . implode(' AND ', $where);
    }

    protected function applyGroup(array $group): string
    {
        return ' GROUP BY ' . implode(', ', $group);
    }

    protected function applySelect(array $select): string
    {
        if (!$select) {
            return '*';
        }

        return implode(', ', $select);
    }

    private function applyLJoin(): ?string
    {
        if (!$this->ljoin) {
            return null;
        }

        $ljoin = [];
        foreach ($this->ljoin as $_table_name => $_condition) {
            if (is_int($_table_name)) {
                $ljoin[] = $_condition;
            } else {
                $ljoin[] = ' LEFT JOIN `' . $_table_name . '` ON (' . $_condition . ')';
            }
        }

        return implode("\n", $ljoin);
    }

    public function setLJoin(array $ljoin): void
    {
        $this->ljoin = $ljoin;
    }
}
