<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Exception;

use Ox\Interop\Fhir\ValueSet\CFHIRIssueType;

/**
 * FHIR Exception
 */
class CFHIRExceptionNotFound extends CFHIRException
{
    /**
     * CFHIRException constructor.
     *
     * @param string $message Message to display
     * @param int    $status_code
     * @param array  $headers
     * @param int    $code    HTTP code
     */
    public function __construct(
        string $message = "Not found",
        int $status_code = 404,
        array $headers = [],
        int $code = 0
    ) {
        parent::__construct($message, $status_code, $headers, $code);

        $this->issueType = CFHIRIssueType::TYPE_NOT_FOUND;
    }
}
