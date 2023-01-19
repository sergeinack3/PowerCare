<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Vital;

class CoverageCodePeriod extends Period
{
    /** @var string */
    protected $ald_code;

    /** @var string */
    protected $situation_code;

    /** @var string */
    protected $standard_exoneration_code;

    /** @var string */
    protected $standard_rate;

    /** @var int */
    protected $alsace_mozelle_flag;

    public function getAldCode(): string
    {
        return $this->ald_code;
    }

    public function getSituationCode(): string
    {
        return $this->situation_code;
    }

    public function getStandardExonerationCode(): string
    {
        return $this->standard_exoneration_code;
    }

    public function getStandardRate(): string
    {
        return $this->standard_rate;
    }

    public function getAlsaceMozelleFlag(): int
    {
        return $this->alsace_mozelle_flag;
    }

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        $data['ald_code'] = $this->ald_code;
        $data['situation_code'] = $this->situation_code;
        $data['standard_exoneration_code'] = $this->standard_exoneration_code;
        $data['alsace_mozelle_flag'] = $this->alsace_mozelle_flag;
        $data['standard_rate'] = $this->standard_rate;

        return $data;
    }
}
