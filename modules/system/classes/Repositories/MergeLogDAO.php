<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Repositories;

use Exception;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\System\CMergeLog;

/**
 * CMergeLog Data Access Object
 */
class MergeLogDAO
{
    private const OP_EQ      = '=';
    private const OP_NEQ     = '!=';
    private const OP_GT      = '>';
    private const OP_GTE     = '>=';
    private const OP_LT      = '<';
    private const OP_LTE     = '<=';
    private const OP_IN      = 'IN';
    private const OP_NIN     = 'NOT IN';
    private const OP_LIKE    = 'LIKE';
    private const OP_NLIKE   = 'NOT LIKE';
    private const OP_BETWEEN = 'BETWEEN';

    private const OPERATORS = [
        self::OP_EQ,
        self::OP_NEQ,
        self::OP_GT,
        self::OP_GTE,
        self::OP_LT,
        self::OP_LTE,
        self::OP_IN,
        self::OP_NIN,
        //self::OP_LIKE, Not used
        self::OP_NLIKE,
        self::OP_BETWEEN,
    ];

    /** @var CMergeLog */
    private $model;

    /** @var CSQLDataSource */
    private $ds;

    /** @var array */
    private $filters = [];

    public function __construct()
    {
        $this->model = new CMergeLog();
        $this->ds    = $this->model->getDS();
    }

    /**
     * @return CMergeLog[]
     * @throws Exception
     */
    public function find(): array
    {
        return $this->model->loadList($this->getWhereClause());
    }

    /**
     * @return int
     * @throws Exception
     */
    public function count(): int
    {
        return (int)$this->model->countList($this->getWhereClause());
    }

    /**
     * @return array
     */
    private function getWhereClause(): array
    {
        $where = [];
        foreach ($this->filters as $_property => $_filter) {
            [$_operator, $_value] = $_filter;

            switch ($_operator) {
                case self::OP_BETWEEN:
                    $where[$_property] = $this->ds->prepare('BETWEEN ?1 AND ?2', ...$_value);
                    break;

                case self::OP_IN:
                    $where[$_property] = $this->ds::prepareIn($_value);
                    break;

                case self::OP_NIN:
                    $where[$_property] = $this->ds::prepareNotIn($_value);
                    break;

                case self::OP_LIKE:
                    $where[$_property] = $this->ds->prepareLike($_value);
                    break;

                default:
                    $where[$_property] = $this->ds->prepare("{$_operator} ?", $_value);
            }
        }

        return $where;
    }

    /**
     * @param string $property
     * @param string $operator
     * @param mixed  $value
     *
     * @return $this
     * @throws Exception
     */
    public function where(string $property, string $operator, $value): self
    {
        if (!in_array($operator, self::OPERATORS)) {
            throw new Exception('Invalid operator');
        }

        if ($operator === self::OP_BETWEEN) {
            if ((!is_array($value) || count($value) !== 2)) {
                throw new Exception('Invalid between');
            }
        } elseif (in_array($operator, [self::OP_IN, self::OP_NIN])) {
            if (!is_array($value) || count($value) < 1) {
                throw new Exception('No empty value');
            }
        } elseif (!is_scalar($value)) {
            throw new Exception('Only scalars');
        }

        if (is_bool($value)) {
            $value = (string)(int)$value;
        }

        $this->filters[$property] = [$operator, $value];

        return $this;
    }

    public function reset(): void
    {
        $this->filters = [];
    }
}
