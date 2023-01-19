<?php

/**
 * @package Mediboard\Fhir\Exception
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Exception;

use Ox\Interop\Fhir\ValueSet\CFHIRIssueType;
use Symfony\Component\HttpFoundation\Response;

class CFHIRExceptionInformational extends CFHIRException
{
    /** @var int */
    public const CODE_RESOURCE_NOT_SUPPORTED_NOW = 10001;

    public function __construct(
        string $message,
        int $status_code = Response::HTTP_INTERNAL_SERVER_ERROR,
        array $headers = [],
        int $code = 0
    ) {
        parent::__construct($message, $status_code, $headers, $code);

        $this->issueType = CFHIRIssueType::TYPE_INFORMATIONAL;
    }

    /**
     * @return static
     */
    public static function invalidSenderConfigs(): self
    {
        return new self("The configuration for sender HTTP is not set");
    }
}
