<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions\HealthInsurance;

use Ox\Mediboard\Jfse\Exceptions\JfseException;

/**
 * Class HealthInsuranceException
 *
 * @package Ox\Mediboard\Jfse\Exceptions\HealthInsurance
 */
class HealthInsuranceException extends JfseException
{
    public static function unknownSearchMode(): self
    {
        return new static('Unknown Search Mode', 'CHealthInsurance-unkown search mode');
    }

    public static function invalidForm(): self
    {
        return new static('Invalid Form', 'CHealthInsurance-invalid form');
    }

    public static function invalidCode(): self
    {
        return new static('Invalid Code', 'CHealthInsurance-invalid code');
    }
}
