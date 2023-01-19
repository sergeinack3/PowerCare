<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files\Exceptions;

use Ox\Core\CMbException;

class FilesCategoryException extends CMbException
{
    public static function groupIsNull(): self
    {
        return new self('CFilesCategory-error-group is null');
    }
}
