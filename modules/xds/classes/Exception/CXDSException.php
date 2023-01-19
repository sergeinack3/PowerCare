<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Exception;

use Ox\Core\CMbException;

class CXDSException extends CMbException {

    /**
     * @return static
     */
    public static function invalidFactoryType(): self
    {
        return new self('CXDSException-error-invalid factory type');
    }

    public static function invalidRequestType(string $type): self
    {
        return new self('CXDSException-error-invalid request type', $type);
    }

    /**
     * @param string $code_system
     *
     * @return static
     */
    public static function invalidCodeSystem(string $code_system): self
    {
        return new self('CXDSException-error-invalid code system', $code_system);
    }

    /**
     * @return static
     */
    public static function invalidObjectConstructSubmissionSet(): self
    {
        return new self('CXDSException-error-invalid object construct submission set');
    }

    /**
     * @return static
     */
    public static function invalidTypeSubmissionSet(): self
    {
        return new self('CXDSException-error-invalid type submission set');
    }

    /**
     * @return static
     */
    public static function moduleNoActif(): self
    {
        return new self('CXDSException-error-module no actif');
    }

    /**
     * @return static
     */
    public static function missingConfigGroup(): self
    {
        return new self('CXDSException-error-missing finess or siret');
    }

    /**
     * @param string $code_system
     *
     * @return static
     */
    public static function invalidSerializerFormat(string $format): self
    {
        return new self('CXDSException-error-invalid serializer format', $format);
    }
}
