<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Security\Csrf\Exceptions;

use Ox\Core\CMbException;

/**
 * Description
 */
class CouldNotGetCsrfToken extends CMbException
{
    /**
     * @return static
     */
    public static function notFound(): self
    {
        return new self('CouldNotGetCsrfToken-error-Token not found.');
    }

    /**
     * @return static
     */
    public static function hasExpired(): self
    {
        return new self('CouldNotGetCsrfToken-error-Token has expired.');
    }

    /**
     * @param string $parameter
     *
     * @return static
     */
    public static function invalidParameter(string $parameter): self
    {
        return new self('CouldNotGetCsrfToken-error-Invalid parameter supplied: %s', $parameter);
    }
}
