<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Exception;

class CCDAExceptionBio extends CCDAException
{
    /**
     * @return static
     */
    public static function identifierNotFound(): self
    {
        //The node "setId" is required in CDA document biology
        return new self('CCDAException-error-medical folder not found');
    }

    /**
     * @return static
     */
    public static function resultSetFailed(string $msg): self
    {
        return new self('CCDAExceptionBio-error-result set impossible to stored', $msg);
    }

    /**
     * @return static
     */
    public static function id400Failed(string $msg): self
    {
        return new self('CCDAExceptionBio-error-idsante failed', $msg);
    }
}
