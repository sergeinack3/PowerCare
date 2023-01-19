<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic\QueryBuilder\Filters;

/**
 * Is build for query combinations
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html Elasticsearch's Documentation
 */
class ElasticQueryBool extends AbstractElasticQueryFilter
{
    private array $bool = [];

    public function addMust(ElasticQueryMust $elastic_query_must): self
    {
        $data = $elastic_query_must->build();
        if ($data !== []) {
            $this->bool["must"] = $data;
        }

        return $this;
    }

    public function addMustNot(ElasticQueryMustNot $elastic_query_must_not): self
    {
        $data = $elastic_query_must_not->build();
        if ($data !== []) {
            $this->bool["must_not"] = $data;
        }

        return $this;
    }

    public function addShould(ElasticQueryShould $elastic_query_should): self
    {
        $data = $elastic_query_should->build();
        if ($data !== []) {
            $this->bool["should"] = $data;
        }

        return $this;
    }

    public function addBoost(float $boost): self
    {
        $this->bool["boost"] = $boost;

        return $this;
    }

    public function addMinimumShouldMatch(string $minimum): self
    {
        $this->bool["minimum_should_match"] = $minimum;

        return $this;
    }

    public function build(): array
    {
        if ($this->bool === []) {
            return [];
        }

        return [
            "bool" => $this->bool,
        ];
    }
}
