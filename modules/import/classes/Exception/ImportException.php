<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Exception;

use Exception;
use Ox\Core\CMbException;

/**
 * Exception class for the import
 */
class ImportException extends CMbException
{
    /**
     * @return void
     * @throws Exception
     */
    public function logError(): void
    {
        // TODO Log the error
    }
}
