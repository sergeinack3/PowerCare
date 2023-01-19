<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic\QueryBuilder\Filters;

use Ox\Core\Elastic\QueryBuilder\RangeOperator;

/**
 * Returns documents that contain terms within a provided range.
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html Elasticsearch's Documentation
 */
class ElasticQueryRange extends AbstractElasticQueryFilter
{
    private string  $field;
    private array   $range;
    private ?float  $boost    = null;
    private ?string $format   = null;
    private ?string $relation = null;

    /**
     * @param string        $field
     * @param RangeOperator $type
     * @param string        $value
     */
    public function __construct(string $field, RangeOperator $type, string $value)
    {
        $this->field                    = $field;
        $this->range[$type->getValue()] = $value;
    }

    public function addConstraint(RangeOperator $type, string $value): self
    {
        $this->range[$type->getValue()] = $value;

        return $this;
    }

    /**
     * @param float $boost
     *
     * @return ElasticQueryRange
     */
    public function setBoost(float $boost): self
    {
        $this->boost = $boost;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return ElasticQueryRange
     */
    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @param string $relation
     *
     * @return ElasticQueryRange
     */
    public function setRelation(string $relation): self
    {
        $this->relation = $relation;

        return $this;
    }

    public function build(): array
    {
        $data = [];
        foreach ($this->range as $_key => $_value) {
            $data["range"][$this->field][$_key] = $_value;
        }

        if ($this->boost !== null) {
            $data["range"][$this->field]["boost"] = $this->boost;
        }

        if ($this->format !== null) {
            $data["range"][$this->field]["format"] = $this->format;
        }

        if ($this->relation !== null) {
            $data["range"][$this->field]["relation"] = $this->relation;
        }

        return $data;
    }
}
