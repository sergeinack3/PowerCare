<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic\QueryBuilder\Filters;

use Ox\Core\Elastic\QueryBuilder\RangeOperator;

/**
 * Abstract query filter builder
 */
abstract class AbstractElasticQueryFilter
{
    protected array $filter = [];

    public static function bool(): ElasticQueryBool
    {
        return new ElasticQueryBool();
    }

    public static function term(string $field, string $value): ElasticQueryTerm
    {
        return new ElasticQueryTerm($field, $value);
    }

    public static function terms(string $field, array $value): ElasticQueryTerms
    {
        return new ElasticQueryTerms($field, $value);
    }

    public static function match(string $field, string $value): ElasticQueryMatch
    {
        return new ElasticQueryMatch($field, $value);
    }

    public static function matchPhrase(string $field, string $value): ElasticQueryMatchPhrase
    {
        return new ElasticQueryMatchPhrase($field, $value);
    }

    public static function range(string $field, RangeOperator $op, string $value): ElasticQueryRange
    {
        return new ElasticQueryRange($field, $op, $value);
    }

    /**
     * @return array
     */
    public function build(): array
    {
        return $this->filter;
    }
}
