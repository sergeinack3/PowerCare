<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Exception;

use Ox\Core\CMbException;

/**
 * Description
 */
class GenericImportException extends CMbException
{
    public static function classIsNotImportable(string $class_name): self
    {
        return new self('GenericImportException-Error-Class is not importable', $class_name);
    }
}
