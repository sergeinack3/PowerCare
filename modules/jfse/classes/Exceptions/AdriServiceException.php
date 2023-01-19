<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions;

class AdriServiceException extends JfseException
{
    public static function missingMandatoryFields(array $fields): self
    {
        return new static(
            'MissingMandatoryFields',
            'AdriServiceException-Missing mandatory fields. Mandatory are: %s',
            [implode(', ', $fields)]
        );
    }
}
