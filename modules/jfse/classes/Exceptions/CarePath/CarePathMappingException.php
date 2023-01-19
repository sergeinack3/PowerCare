<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions\CarePath;

use Ox\Mediboard\Jfse\Domain\CarePath\CarePathEnum;
use Ox\Mediboard\Jfse\Exceptions\JfseException;
use Throwable;

class CarePathMappingException extends JfseException
{
    final public function __construct(
        string $name,
        string $locale,
        array $locale_args = [],
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($name, $locale, $locale_args, $code, $previous);
    }

    public static function wrongInvoicingIdSize(): self
    {
        return new static('InvoicingIdWrongSize', 'CarePathDoctor-WrongInvoicingIdSize');
    }

    public static function invoicingIdMustBeNumeric(): self
    {
        return new static('InvoicingIdMustBeNumeric', 'CarePathDoctor-InvoicingIdMustBeNumeric');
    }

    public static function missingDeclaration(string $indicator): self
    {
        return new static('MissingDeclarationField', 'CarePath-Missing declaration', [$indicator]);
    }

    public static function missingInstallDate(): self
    {
        return new static(
            'MissingInstallDateField',
            'Missing install date for the indicator',
            [CarePathEnum::RECENTLY_INSTALLED_RP()]
        );
    }

    public static function missingPoorMdZoneInstallDate(): self
    {
        return new static(
            'MissingPoorMdZoneInstallDateField',
            'CarePath-Missing poor medicalized zone install date',
            [CarePathEnum::RECENTLY_INSTALLED_RP()]
        );
    }

    public static function missingDoctor(): self
    {
        return new static(
            'MissingDoctorField',
            'Missing doctor fields for the indicator ',
            [CarePathEnum::ORIENTED_BY_RP() . ' ou ' . CarePathEnum::ORIENTED_BY_NRP()]
        );
    }
}
