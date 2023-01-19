<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Utilities\SearchParameters;

use Ox\Core\Api\Request\RequestFilter;
use Ox\Core\CMbDT;

class SearchParameterDate extends AbstractSearchParameter
{
    /** @var string[] */
    public const ACCEPTED_PREFIXES = self::ALL_PREXFIX;

    /**
     * @inheritDoc
     */
    public function prepareOperator(?string $modifier, $value, ?string $prefixValue): string
    {
        switch ($prefixValue) {
            case self::PREFIX_NOT_EQUAL:
                return RequestFilter::FILTER_NOT_EQUAL;
            case self::PREFIX_GREATER_THAN:
                return RequestFilter::FILTER_GREATER;
            case self::PREFIX_GREATER_OR_EQUAL:
                return RequestFilter::FILTER_GREATER_OR_EQUAL;
            case self::PREFIX_LESS_THAN:
                return RequestFilter::FILTER_LESS;
            case self::PREFIX_LESS_OR_EQUAL:
                return RequestFilter::FILTER_LESS_OR_EQUAL;
            case self::PREFIX_START_AFTER:
            case self::PREFIX_END_BEFORE:
            case self::PREFIX_APPROXIMATE:
            case self::PREFIX_EQUAL:
            default:
                return RequestFilter::FILTER_EQUAL;
        }
    }

    /**
     * @param string $value
     *
     * @return string|null
     */
    public function extractPrefixValue(string $value): ?string
    {
        $prefix = substr($value, 0, 2);

        return in_array($prefix, self::ALL_PREXFIX) ? $prefix : null;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function extractValue($value)
    {
        $value = $this->extractPrefixValue($value) ? substr($value, 2) : $value;

        return CMbDT::date($value);
    }
}
