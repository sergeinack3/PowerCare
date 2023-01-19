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
class CouldNotValidateToken extends CMbException
{
    /**
     * @return static
     */
    public static function tokenNotProvided(): self
    {
        return new self('CouldNotValidateToken-error-Token not provided.');
    }

    /**
     * @return static
     */
    public static function tokenDoesNotMatch(): self
    {
        return new self('CouldNotValidateToken-error-Token does not match.');
    }

    /**
     * @return static
     */
    public static function parametersAreNotValid(): self
    {
        return new self('CouldNotValidateToken-error-Token parameters are not valid .');
    }

    /**
     * @return static
     */
    public static function unsupportedBody(): self
    {
        return new self('CouldNotValidateToken-error-Body format is not supported.');
    }
}
