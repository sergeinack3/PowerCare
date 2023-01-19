<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic\QueryBuilder\Filters;

/**
 * Returns documents that contain an exact term in a provided field.
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html Elasticsearch's Documentation
 */
class ElasticQueryTerm extends AbstractElasticQueryFilter
{
    private string $field;
    private string $value;
    private ?float $boost = null;

    /**
     * @param string $field
     * @param string $value
     */
    public function __construct(string $field, string $value)
    {
        $this->field = $field;
        $this->value = $value;
    }

    /**
     * @param float $boost
     */
    public function setBoost(float $boost): void
    {
        $this->boost = $boost;
    }

    public function build(): array
    {
        $data = [
            "term" => [
                $this->field => $this->value,
            ],
        ];

        if ($this->boost !== null) {
            $data["term"]["boost"] = $this->boost;
        }

        return $data;
    }
}
