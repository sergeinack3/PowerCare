<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Exception;

use Ox\Core\CMbException;

class CCDAException extends CMbException
{
    /**
     * @return static
     */
    public static function errorParse(): self
    {
        return new self('CCDAException-error-error parse');
    }

    /**
     * @return static
     */
    public static function eventCDANotFound(): self
    {
        return new self('CCDAException-error-event CDA not found');
    }

    /**
     * @return static
     */
    public static function invalidDocument(): self
    {
        return new self('CCDAException-error-invalid document CDA');
    }

    /**
     * @return static
     */
    public static function invalidFactoryType(): self
    {
        return new self('CCDAException-error-invalid factory type');
    }

    /**
     * @return static
     */
    public static function invalidCoherenceFactoryParameters(): self
    {
        return new self('CCDAException-error-invalid coherence factory parameters');
    }

    /**
     * @return static
     */
    public static function invalidType(): self
    {
        return new self('CCDAException-error-invalid type');
    }

    /**
     * @return static
     */
    public static function factoryNotFound(): self
    {
        return new self('CCDAException-error-factory not found');
    }

    /**
     * @return static
     */
    public static function handlerNotFound(): self
    {
        return new self('CCDAException-error-handler not found');
    }


    /**
     * @return static
     */
    public static function patientIdentifierNotFound(): self
    {
        return new self('CCDAException-error-patient identifier not found');
    }

    /**
     * @return static
     */
    public static function patientNotFound(): self
    {
        return new self('CCDAException-error-patient not found');
    }

    /**
     * @return static
     */
    public static function errorStoreCDAFile(string $msg): self
    {
        return new self('CCDAException-error-error store CDAr2 file', $msg);
    }

    /**
     * @return static
     */
    public static function noTargetToSaveFile(): self
    {
        return new self('CCDAExceptionLevel1-error-no target to save file');
    }

    /**
     * @return static
     */
    public static function missingParameter(string $parameter): self
    {
        return new self('CCDAException-error-missing parameter', $parameter);
    }
}
