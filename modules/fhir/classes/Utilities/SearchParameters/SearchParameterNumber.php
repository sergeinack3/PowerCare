<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Utilities\SearchParameters;

class SearchParameterNumber extends AbstractSearchParameter
{
    /**
     * @inheritDoc
     */
    public function prepareOperator(?string $modifier, $value, ?string $prefixValue): string
    {
        //todo
        return '';
    }
}
