<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic\QueryBuilder;

/**
 * Regroup data on specific fields with different types of aggregation (max, sum, words, ..)
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations.html Elasticsearch's Documentation
 */
class ElasticQueryAggregation
{
    private array $aggregation = [];

    public function addCardinality(string $field_name, string $field): self
    {
        $this->aggregation[$field_name]["cardinality"]["field"] = $field;

        return $this;
    }

    public function addCardinalityByScript(string $field_name, string $script): self
    {
        $this->aggregation[$field_name]["cardinality"]["script"] = $script;

        return $this;
    }

    public function addTerms(string $field_name, string $field, int $size): self
    {
        $this->aggregation[$field_name]["terms"]["field"] = $field;
        $this->aggregation[$field_name]["terms"]["size"]  = $size;

        return $this;
    }

    public function addSum(string $field_name, string $field): self
    {
        $this->aggregation[$field_name]["sum"]["field"] = $field;

        return $this;
    }

    public function addMax(string $field_name, string $field): self
    {
        $this->aggregation[$field_name]["max"]["field"] = $field;

        return $this;
    }

    public function addMin(string $field_name, string $field): self
    {
        $this->aggregation[$field_name]["min"]["field"] = $field;

        return $this;
    }

    public function addBucketSort(string $field_name, int $size, int $from): self
    {
        $this->aggregation[$field_name]["bucket_sort"]["size"] = $size;
        $this->aggregation[$field_name]["bucket_sort"]["from"] = $from;

        return $this;
    }

    public function addTopHit(string $field_name): self
    {
        $this->aggregation[$field_name]["top_hits"]["size"] = 1;

        return $this;
    }

    public function addSubAggregation(string $field_name, ElasticQueryAggregation $aggregation): self
    {
        $this->aggregation[$field_name]["aggs"] = $aggregation->build();

        return $this;
    }

    public function addSortOnAggregation(string $aggregation_field, string $field, string $type): self
    {
        $this->aggregation[$aggregation_field][array_keys(
            $this->aggregation[$aggregation_field]
        )[0]]["sort"][$field] = $type;

        return $this;
    }

    public function addOrderOnAggregation(string $aggregation_field, string $field, string $type): self
    {
        $this->aggregation[$aggregation_field][array_keys(
            $this->aggregation[$aggregation_field]
        )[0]]["order"][$field] = $type;

        return $this;
    }

    public function addMultiTermsAggregation(string $field_name, array $fields, int $size): self
    {
        $this->aggregation[$field_name]["multi_terms"]["size"] = $size;
        foreach ($fields as $_field) {
            $this->aggregation[$field_name]["multi_terms"]["terms"][]["field"] = $_field;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function build(): array
    {
        return $this->aggregation;
    }
}
