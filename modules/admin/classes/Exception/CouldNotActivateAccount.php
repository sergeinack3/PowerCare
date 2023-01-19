<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\Exception;

use Ox\Core\CMbException;

/**
 * Exception class when an account activation token cannot be generated (or the mail cannot be send).
 */
class CouldNotActivateAccount extends CMbException
{
    /**
     * @return static
     */
    public static function userNotFound(): self
    {
        return new static('CouldNotActivateAccount-error-User not found.');
    }

    /**
     * @return static
     */
    public static function sourceNotFound(): self
    {
        return new static('CouldNotActivateAccount-error-Source not found.');
    }

    /**
     * @return static
     */
    public static function sourceNotEnabled(): self
    {
        return new static('CouldNotActivateAccount-error-Source not enabled.');
    }

    /**
     * @return static
     */
    public static function superAdminNotAllowed(): self
    {
        return new static('CouldNotActivateAccount-error-Super admin is not allowed here.');
    }

    /**
     * @param string $message
     *
     * @return static
     */
    public static function unableToResetPassword(string $message): self
    {
        return new static($message);
    }

    /**
     * @param string $message
     *
     * @return static
     */
    public static function unableToCreateToken(string $message): self
    {
        return new static($message);
    }

    /**
     * @param string $email
     *
     * @return static
     */
    public static function invalidEmail(string $email): self
    {
        return new static('CouldNotActivateAccount-error-Provided email is invalid: %s.', $email);
    }

    /**
     * @param string $message
     *
     * @return static
     */
    public static function unableToSendEmail(string $message): self
    {
        return new static($message);
    }
}
