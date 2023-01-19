<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions;

use Exception;

final class VitalException extends JfseException
{
    /**
     * @return static
     */
    public static function unknownBeneficiary(): self
    {
        return new static('UnknownBeneficiary', 'VitalCard-error-unknown_beneficiary');
    }

    public static function missingData(): self
    {
        return new static('MissingDta', 'VitalCard-error-missing_data');
    }
}
