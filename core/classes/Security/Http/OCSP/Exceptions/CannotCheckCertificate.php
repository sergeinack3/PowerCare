<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Security\Http\OCSP\Exceptions;

use Ox\Core\CMbException;

/**
 * Exception threw before an OCSP call.
 */
class CannotCheckCertificate extends CMbException
{
    public static function curlVersionNotSupported(string $version, string $expected): self
    {
        return new self(
            "CannotCheckCertificate-error-cURL version is not supported: %s, expected at least: %s",
            $version,
            $expected
        );
    }
}
