<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\MedicalAct;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class CommonPrevention extends AbstractEntity
{
    /** @var int */
    protected $prevention_top;

    /** @var string */
    protected $qualifier;

    /**
     * @return int
     */
    public function getPreventionTop(): ?int
    {
        return $this->prevention_top;
    }

    /**
     * @return string
     */
    public function getQualifier(): ?string
    {
        return $this->qualifier;
    }
}
