<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Vital;

class AmoServicePeriod extends Period
{
    /** @var string */
    protected $code;

    /** @var string */
    protected $label;

    /** @var string */
    protected $ruf_data;

    /**
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }
    /**
     * @return string
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getRufData(): ?string
    {
        return $this->ruf_data;
    }

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        $data['code'] = $this->code;
        $data['label'] = utf8_encode($this->label ?? '');
        $data['ruf_data'] = utf8_encode($this->ruf_data ?? '');

        return $data;
    }
}
