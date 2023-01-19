<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Api;

/**
 * Represents an error returned by the Jfse API
 * @package Ox\Mediboard\Jfse\API
 */
final class Error
{
    /** @var int The Jfse internal error code */
    private $code;

    /** @var string The name of the method that generated the exception */
    private $source;

    /** @var string The description of the error */
    private $description;

    /** @var string Additional details on the error */
    private $details;

    /** @var array */
    public static $general_error_codes = [
        1, 2, 3, 4, 5, 6, 7, 8, 1001, 1002, 1004, 1019, 2002, 2003, 2004, 3009, 4002, 4003, 4005, 4007, 5000, 5001
    ];

    /** @var int The code indicating that the API returned an error */
    public const GENERAL_API_ERROR = 2001;

    /**
     * Error constructor.
     *
     * @param int         $code
     * @param string      $source
     * @param string      $description
     * @param string|null $details
     */
    private function __construct(int $code, string $source, string $description, ?string $details = null)
    {
        $this->code        = $code;
        $this->source      = $source;
        $this->description = $description;
        $this->details     = $details;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getDetails(): ?string
    {
        return $this->details;
    }

    /**
     * Creates an instance of Error from the given array
     *
     * @param array $data The data
     *
     * @return static
     */
    public static function map(array $data): self
    {
        $code        = $data['code'] ?? 0;
        $source      = $data['source'] ?? '';
        $description = $data['description'] ?? '';
        $details     = $data['details'] ?? null;


        return new self($code, $source, $description, $details);
    }
}
