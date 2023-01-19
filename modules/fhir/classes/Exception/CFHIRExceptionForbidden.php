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

class CFHIRExceptionForbidden extends CFHIRException
{
    public function __construct(
        string $message = "You are not authorized to access at this resource",
        int $status_code = Response::HTTP_FORBIDDEN,
        array $headers = [],
        int $code = 0
    ) {
        parent::__construct($message, $status_code, $headers, $code);

        $this->issueType = CFHIRIssueType::TYPE_FORBIDDEN;
        $this->severity  = CFHIRIssueSeverity::SEVERITY_FATAL;
    }
}
