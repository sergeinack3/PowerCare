<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Exceptions;

/**
 * Description
 */
class CanNotMerge extends MergeException
{
    public static function invalidObject(): self
    {
        return new static('mergeNotCMbObject');
    }

    public static function objectNotFound(): self
    {
        return new static('mergeNoId');
    }

    public static function differentType(): self
    {
        return new static('mergeDifferentType');
    }
}
