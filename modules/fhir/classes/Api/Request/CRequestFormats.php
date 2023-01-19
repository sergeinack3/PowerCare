<?php

/**
 * @package Mediboard\Fhir\Request\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Api\Request;

use Ox\Core\Api\Request\IRequestParameter;
use Ox\Core\CMbArray;
use Symfony\Component\HttpFoundation\Request;

class CRequestFormats implements IRequestParameter
{
    /** @var string */
    public const CONTENT_TYPE_XML = "application/fhir+xml";

    /** @var string */
    public const KEY_RESOURCE_CONTENT_TYPE = 'resource_content_type';

    /** @var Request */
    private $request;
    /** @var string */
    private $format;

    /** @var string */
    public const CONTENT_TYPE_JSON = "application/fhir+json";

    /**
     * CRequestFormats constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        $request_format = $request->get("_format") ?? CMbArray::get($request->getAcceptableContentTypes(), 0);
        if (!$format = $this->getFormatSupported($request_format)) {
            $format = self::CONTENT_TYPE_XML;
        }

        $this->format = $format;
        $request->attributes->set(self::KEY_RESOURCE_CONTENT_TYPE, $this->format);
    }

    /**
     * @return Request
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @return bool
     */
    public function isFormatJSON(): bool
    {
        return $this->getFormat() === self::CONTENT_TYPE_JSON;
    }

    /**
     * @return bool
     */
    public function isFormatXML(): bool
    {
        return $this->getFormat() === self::CONTENT_TYPE_XML;
    }

    /**
     * @return bool
     */
    public function isContentTypeJSON(): bool
    {
        $content_type = $this->request->headers->get('Content-Type', self::CONTENT_TYPE_JSON);
        $content_type = self::getFormatSupported($content_type);

        return $content_type === self::CONTENT_TYPE_JSON;
    }

    /**
     * @return bool
     */
    public function isContentTypeXML(): bool
    {
        $content_type = $this->request->headers->get('Content-Type', self::CONTENT_TYPE_JSON);
        $content_type = self::getFormatSupported($content_type);

        return $content_type === self::CONTENT_TYPE_XML;
    }
    /**
     * Get format supported
     *
     * @param $format
     *
     * @return string format
     */
    public static function getFormatSupported(?string $format): ?string
    {
        switch ($format) {
            case "application/fhir+xml":
            case "application/xml+fhir":
            case "application/xml":
            case "xml":
                return self::CONTENT_TYPE_XML;
            case "application/fhir+json":
            case "application/json+fhir":
            case "application/json":
            case "json":
                return self::CONTENT_TYPE_JSON;
            default:
                return null;
        }
    }
}
