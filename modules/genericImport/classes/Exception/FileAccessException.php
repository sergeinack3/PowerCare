<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Exception;

/**
 * Description
 */
class FileAccessException extends GenericImportException
{
    /**
     * FileAccessException constructor.
     *
     * @param string $text
     * @param        ...
     */
    public function __construct()
    {
        parent::__construct(func_get_args());
    }

    public static function UnableToInitDirException(string $dir): self
    {
        return new self('FileAccessException-Error-Unable to access dir', $dir);
    }

    public static function UnableToOpenFileForWriting(string $file): self
    {
        return new self('FileAccessException-Error-Unable to open file for writing', $file);
    }
}
