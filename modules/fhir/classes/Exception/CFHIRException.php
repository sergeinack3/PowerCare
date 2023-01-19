<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Exception;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\Kernel\Exception\HttpException;
use Ox\Interop\Fhir\Api\Response\CFHIRResponse;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\OperationOutcome\CFHIRDataTypeOperationOutcomeIssue;
use Ox\Interop\Fhir\Interactions\CFHIRInteraction;
use Ox\Interop\Fhir\Resources\R4\OperationOutcome\CFHIRResourceOperationOutcome;
use Ox\Interop\Fhir\ValueSet\CFHIRIssueSeverity;
use Ox\Interop\Fhir\ValueSet\CFHIRIssueType;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

/**
 * FHIR Exception
 */
class CFHIRException extends HttpException
{
    /** @var string */
    protected $severity;

    /** @var string */
    protected $issueType;

    /**
     * CFHIRException constructor.
     *
     * @param string $message     Message to display
     * @param int    $status_code HTTP code
     * @param array  $headers
     * @param int    $code        Internal code
     */
    public function __construct(
        string $message = "An error occured",
        int $status_code = 422,
        array $headers = [],
        int $code = 0
    ) {
        parent::__construct($status_code, $message, $headers, $code);
    }

    /**
     * Make a exception and Localize given message
     *
     * @param string $message
     * @param string ...$args
     *
     * @return static
     */
    public static function tr(string $message, string ...$args)
    {
        $message = CAppUI::tr($message, ...$args);

        return new static($message);
    }

    /**
     * @param Exception $exception
     *
     * @return self
     */
    public static function convert(Exception $exception)
    {
        $headers = [];
        $code = $exception->getCode();
        $status_code = 500;
        if ($exception instanceof HttpException) {
            $status_code = $exception->getStatusCode();
            $headers = $exception->getHeaders();
        }

        return new CFHIRException($exception->getMessage(), $status_code, $headers, $code);
    }

    /**
     * @return string
     */
    public function getSeverity(): string
    {
        return $this->severity ?? CFHIRIssueSeverity::SEVERITY_ERROR;
    }

    /**
     * @return string
     */
    public function getIssueType(): string
    {
        return $this->issueType ?? CFHIRIssueType::TYPE_EXCEPTION;
    }

    /**
     * @param string $severity
     */
    public function setSeverity(string $severity): void
    {
        $this->severity = $severity;
    }

    /**
     * @param string $issueType
     */
    public function setIssueType(string $issueType): void
    {
        $this->issueType = $issueType;
    }

    /**
     * @param string $format
     *
     * @return Response
     * @throws InvalidArgumentException
     */
    public function makeResponse(string $format): Response
    {
        $resource = new CFHIRResourceOperationOutcome();
        $resource->addIssue(
            CFHIRDataTypeOperationOutcomeIssue::build(
                [
                    "severity"    => $this->getSeverity(),
                    "code"        => $this->getIssueType(),
                    "diagnostics" => $this->getMessage(),
                ]
            )
        );

        $interaction = new CFHIRInteraction();
        $interaction->format = $format;
        $interaction->setResource($resource);

        $resourceResponse = new CFHIRResponse($interaction, $format);
        $response         = $resourceResponse->output();

        // set response
        $response->setStatusCode($this->getStatusCode(), $this->getMessage());
        $response->headers->add($this->headers);
        $response->headers->set("content-type", $format);

        return $response;
    }
}
