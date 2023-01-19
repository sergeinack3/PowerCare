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
class CouldNotUseCsrf extends CMbException
{
    /**
     * @return static
     */
    public static function alreadyInitialized(): self
    {
        return new self('CouldNotUseCsrf-error-Already initialized');
    }

    /**
     * @return static
     */
    public static function notInitialized(): self
    {
        return new self('CouldNotUseCsrf-error-Not initialized');
    }
}
