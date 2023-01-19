<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Vital;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class WorkAccident extends AbstractEntity
{
    /** @var int - belongs to [1,3] (AT1, AT2, AT3) */
    protected $number;

    /** @var int */
    protected $group;

    /** @var string */
    protected $recipient_organisation;

    /** @var string */
    protected $code;

    /** @var string */
    protected $id;
}
