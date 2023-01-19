<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam\Exceptions;

use Ox\Core\CMbException;

/**
 * Exception for CCAM
 */
class CCAMException extends CMbException
{
    public static function codeNotFound(): self
    {
        return new self("CActeCCAM-error-code_not found");
    }
}
