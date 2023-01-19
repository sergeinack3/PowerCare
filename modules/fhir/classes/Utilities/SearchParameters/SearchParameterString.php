<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Utilities\SearchParameters;

use Ox\Core\Api\Request\RequestFilter;

class SearchParameterString extends AbstractSearchParameter
{
    /** @var string[] */
    protected const ACCEPTED_MODIFIERS = [
        self::MODIFIER_CONTAINS,
        self::MODIFIER_EXACT
    ];

    /**
     * @inheritDoc
     */
    public function prepareOperator(?string $modifier, $value, ?string $prefixValue): string
    {
        if (!$modifier) {
            return RequestFilter::FILTER_BEGIN_WITH;
        }

        if ($modifier === self::MODIFIER_CONTAINS) {
            return RequestFilter::FILTER_CONTAINS;
        }

        // modifier EXACT
        if ($modifier === self::MODIFIER_EXACT) {
            return RequestFilter::FILTER_STRICT_EQUAL;
        }

        return parent::prepareOperator($modifier, $value, $prefixValue);
    }
}
