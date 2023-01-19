<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Security\Http\OCSP\Exceptions;

use Ox\Core\CMbException;

/**
* Exception threw during an OCSP call.
*/
class CouldNotCheckCertificate extends CMbException
{
    public static function isNotComplete(): self
    {
        return new self("CouldNotCheckCertificate-error-Certificate is not complete");
    }

    public static function cannotBuild(string $message): self
    {
        return new self("CouldNotCheckCertificate-error-Cannot build OCSP Response '%s'", $message);
    }
}
