<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic\QueryBuilder\Filters;

/**
 * Returns documents that contain one or more exact terms in a provided field.
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-query.html Elasticsearch's Documentation
 */
class ElasticQueryTerms extends AbstractElasticQueryFilter
{
    private string  $field;
    private array   $value;
    private ?float  $boost                = null;
    private ?int $minimum_should_match = null;

    /**
     * @param string $field
     * @param array  $value
     */
    public function __construct(string $field, array $value)
    {
        $this->field = $field;
        $this->value = $value;
    }

    /**
     * @param float $boost
     *
     * @return ElasticQueryTerms
     */
    public function setBoost(float $boost): self
    {
        $this->boost = $boost;

        return $this;
    }

    /**
     * @param int $minimum_should_match
     *
     * @return ElasticQueryTerms
     */
    public function setMinimumShouldMatch(int $minimum_should_match): self
    {
        $this->minimum_should_match = $minimum_should_match;

        return $this;
    }

    public function build(): array
    {
        $data = [
            "terms" => [
                $this->field => $this->value,
            ],
        ];

        if ($this->boost !== null) {
            $data["terms"]["boost"] = $this->boost;
        }

        if ($this->minimum_should_match !== null) {
            $data["terms"]["minimum_should_match"] = $this->minimum_should_match;
        }

        return $data;
    }
}
