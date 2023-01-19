<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Exception;

class CCDAExceptionIJBComorbidite extends CCDAException
{
    /**
     * @return static
     */
    public static function invalidMedicalFolder(): self
    {
        return new self('CCDAException-error-medical folder not found');
    }
}
