<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic\QueryBuilder\Filters;

use Ox\Core\Elastic\QueryBuilder\RangeOperator;

/**
 * Prepare simple research filter for everyday usage
 */
abstract class AbstractElasticQuerySubFilter
{
    protected array $sub_filter = [];


    public function addFilter(AbstractElasticQueryFilter $elastic_query_filter): self
    {
        $data = $elastic_query_filter->build();
        if (count($data) > 0) {
            $this->sub_filter[] = $data;
        }

        return $this;
    }

    public function beIn(string $field, array $value): self
    {
        $this->sub_filter[] = AbstractElasticQueryFilter::terms($field, $value)->build();

        return $this;
    }

    public function beLike(string $field, string $value): self
    {
        $this->sub_filter[] = AbstractElasticQueryFilter::matchPhrase($field, $value)->build();

        return $this;
    }

    public function beInRange(string $field, RangeOperator $type, string $value): self
    {
        $this->sub_filter[] = AbstractElasticQueryFilter::range($field, $type, $value)->build();

        return $this;
    }

    public function beInBetween(string $field, string $min, string $max): self
    {
        $range = AbstractElasticQueryFilter::range($field, RangeOperator::GREATER_THAN_OR_EQUAL_TO(), $min)
            ->addConstraint(RangeOperator::LESS_THAN_OR_EQUAL_TO(), $max);


        $this->sub_filter[] = $range->build();
        return $this;
    }

    /**
     * @return array
     */
    public function build(): array
    {
        return $this->sub_filter;
    }
}
