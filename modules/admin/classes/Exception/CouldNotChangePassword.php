<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\Exception;

use Exception;

/**
 * Description
 */
class CouldNotChangePassword extends Exception
{
    public static function passwordMismatch(): self
    {
        return new self('CUser-user_password-nomatch');
    }

    public static function changingForbidden(): self
    {
        return new self('CUser-password_change_forbidden');
    }

    public static function newPasswordsMismatch(): self
    {
        // Other translation key
        return new self('CUser-user_password-nomatch');
    }
}
