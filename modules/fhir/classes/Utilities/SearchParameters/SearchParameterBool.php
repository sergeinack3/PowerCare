<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Utilities\SearchParameters;

use Ox\Core\Api\Request\RequestFilter;

class SearchParameterBool extends AbstractSearchParameter
{
    /** @var string[] */
    protected const ACCEPTED_MODIFIERS = [];

    /**
     * @inheritDoc
     */
    public function prepareOperator(?string $modifier, $value, ?string $prefixValue): string
    {
        return RequestFilter::FILTER_EQUAL;
    }

    public function extractValue($value)
    {
        if ($value === "false" || $value === 0) {
            return false;
        }

        return boolval($value);
    }
}
