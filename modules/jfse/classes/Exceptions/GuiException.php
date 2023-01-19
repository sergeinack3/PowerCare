<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions;

class GuiException extends JfseException
{
    public static function invalidVitalData(): self
    {
        return new static('InvalidVitalCardData', 'JfseGui-error-vital_reading_error');
    }
}
