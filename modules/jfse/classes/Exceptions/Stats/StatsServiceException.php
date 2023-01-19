<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions\Stats;

use Ox\Mediboard\Jfse\Exceptions\JfseException;

class StatsServiceException extends JfseException
{
    public static function choiceNotFound(): self
    {
        return new static('ChoiceNotFound', 'StatsServiceException-Choice not found');
    }
}
