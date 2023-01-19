<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie\Exceptions;

use Ox\Core\CMbException;

class MessagerieLinkException extends CMbException
{
    public static function instanceOfObjectNotAvailable(): self
    {
        return new self('MessagerieLinkException-Type-attachment-is-not-available-for-this-link');
    }
}
