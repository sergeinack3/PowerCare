<?php

/**
 * @package Mediboard\Fhir\Objects
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Utilities\Helper;

use Ox\Core\CMbException;

class DocumentHelper
{
    /**
     * Get content type
     *
     * @param string $mimetype
     *
     * @return string
     * @throws CMbException
     */
    public static function getContentType(string $mimetype): string
    {
        switch ($mimetype) {
            case "image/jpg":
                $mimetype = "image/jpeg";
                break;
            case "application/rtf":
                $mimetype = "text/rtf";
                break;
            case "image/tiff":
            case "image/jpeg":
            case "application/pdf":
            case "application/xml":
            case "text/plain":
                break;
            default:
                throw new CMbException("fhir-msg-Document type authorized in FHIR|pl", $mimetype);
        }

        return $mimetype;
    }
}
