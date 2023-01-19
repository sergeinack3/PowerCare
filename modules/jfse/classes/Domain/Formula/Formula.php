<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Formula;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

/**
 * Class Formula
 *
 * @package Ox\Mediboard\Jfse\Domain\Formula
 */
final class Formula extends AbstractEntity
{
    /** @var int */
    protected $formula_id;

    /** @var float */
    protected $pmss;

    /** @var string */
    protected $prestation_number;

    /** @var string */
    protected $formula_number;

    /** @var string */
    protected $label;

    /** @var string */
    protected $theoretical_calculation;

    /** @var bool */
    protected $sts;

    /** @var array */
    protected $parameters;

    /**
     * @return int
     */
    public function getFormuleId(): ?int
    {
        return $this->formula_id;
    }

    /**
     * @return float
     */
    public function getPmss(): ?float
    {
        return $this->pmss;
    }

    /**
     * @return string
     */
    public function getPrestationNumber(): ?string
    {
        return $this->prestation_number;
    }

    /**
     * @return string
     */
    public function getFormulaNumber(): ?string
    {
        return $this->formula_number;
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
    public function getTheoreticalCalculation(): ?string
    {
        return $this->theoretical_calculation;
    }

    /**
     * @return bool
     */
    public function getSts(): ?bool
    {
        return $this->sts;
    }

    /**
     * @return array
     */
    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    public function setParametersFromArray(array $parameters): self
    {
        $this->parameters = [];
        foreach ($parameters as $parameter) {
            if (!is_float($parameter['value'])) {
                $parameter['value'] = floatval($parameter['value']);
            }
            $this->parameters[] = Parameter::hydrate($parameter);
        }

        return $this;
    }
}
