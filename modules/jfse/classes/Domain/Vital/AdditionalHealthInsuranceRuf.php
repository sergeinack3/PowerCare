<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Vital;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class AdditionalHealthInsuranceRuf extends AbstractEntity
{
    /** @var int */
    protected $group;

    /** @var string */
    protected $data;
}
