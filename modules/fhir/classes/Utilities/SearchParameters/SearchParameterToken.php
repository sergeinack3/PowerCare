<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Utilities\SearchParameters;

class SearchParameterToken extends AbstractSearchParameter
{
    /**
     * @param $value
     *
     * @return ParameterToken
     */
    public function extractValue($value)
    {
        return new ParameterToken($value);
    }

    /**
     * @param string $prefix
     *
     * @return bool
     */
    public function isSupportedPrefix(string $prefix): bool
    {
        return false;
    }
}
