<?php

/**
 * @package Mediboard\Fhir\Exception
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Exception;

use Ox\Interop\Fhir\ValueSet\CFHIRIssueSeverity;
use Ox\Interop\Fhir\ValueSet\CFHIRIssueType;
use Symfony\Component\HttpFoundation\Response;

class CFHIRExceptionNotSupported extends CFHIRException
{
    public function __construct(
        string $message,
        int $status_code = Response::HTTP_BAD_REQUEST,
        array $headers = [],
        int $code = 0
    ) {
        parent::__construct($message, $status_code, $headers, $code);

        $this->issueType = CFHIRIssueType::TYPE_NOT_SUPPORTED;
        $this->severity = CFHIRIssueSeverity::SEVERITY_FATAL;
    }
}
