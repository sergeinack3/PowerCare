<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions\Cps;

use Ox\Mediboard\Jfse\Exceptions\JfseException;

final class CpsException extends JfseException
{
    public static function invalidCode(): CpsException
    {
        return new static('InvalidCpsCode', 'CpsService-error-invalid_code', []);
    }

    public static function readingError(string $description, string $details): CpsException
    {
        return new static('CpsReadingError', 'CpsService-error-read', [$description, $details]);
    }

    public static function noSituationSelected(): self
    {
        return new static('CpsNoSituationSelected', 'CpsService-error-cps_situation_must_be_selected');
    }

    public static function unknownSituationId(int $situation_id): self
    {
        return new static('CpsUnknownSituationId', 'CpsService-error-situation_id_no_found', [$situation_id]);
    }

    public static function unauthorizedSpeciality(string $code, string $label): self
    {
        return new static('CpsUnauthorizedSpeciality', 'CpsService-error-unauthorized_speciality', [$code, $label]);
    }
}
