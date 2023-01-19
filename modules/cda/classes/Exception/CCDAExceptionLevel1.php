<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Exception;

class CCDAExceptionLevel1 extends CCDAException
{
    /**
     * @return static
     */
    public static function unknownMediaType(): self
    {
        return new self('CCDAExceptionLevel1-error-unknown media type');
    }

    /**
     * @return static
     */
    public static function notBase64File(): self
    {
        return new self('CCDAExceptionLevel1-error-not a base64 file');
    }

    /**
     * @return static
     */
    public static function errorStoreEmbedCDAFile(string $msg): self
    {
        return new self('CCDAExceptionLevel1-error-error store CDA file', $msg);
    }
}
