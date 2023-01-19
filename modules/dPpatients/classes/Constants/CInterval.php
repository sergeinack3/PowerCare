<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Constants;

use Ox\Core\CMbArray;

/**
 * Description Abstract class
 */
abstract class CInterval extends CAbstractConstant
{
    /** @var int|string */
    public $max_value;
    /** @var int|string */
    public $min_value;

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props               = parent::getProps();
        $props["releve_id"]  .= " back|releve_interval";
        $props["patient_id"] .= " back|patient_interval";

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function storeValues(array $values): CAbstractConstant
    {
        if (($min_value = CMbArray::get($values, "min_value")) === null) {
            throw new CConstantException(CConstantException::VALUE_NOT_FOUND);
        }

        if (($max_value = CMbArray::get($values, "max_value")) === null) {
            throw new CConstantException(CConstantException::VALUE_NOT_FOUND);
        }
        $this->min_value = $min_value;
        $this->max_value = $max_value;
        if ($msg = $this->store()) {
            $this->treatErrorStore($msg);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return ["min_value" => $this->min_value, "max_value" => $this->max_value];
    }

    /**
     * @inheritdoc
     */
    public function matchingValues(array $values): bool
    {
        $min_value = CMbArray::get($values, "min_value");
        $max_value = CMbArray::get($values, "max_value");

        return $min_value == $this->min_value && $max_value == $this->max_value;
    }

    /**
     * @param array $data
     *
     * @return CAbstractConstant
     */
    public function map(array $data): CAbstractConstant
    {
        $this->min_value = CMbArray::get($data, 'min_value');
        $this->max_value = CMbArray::get($data, 'max_value');

        return parent::map($data);
    }

    /**
     * @return array
     */
    public function extractValues(): array
    {
        $values = ['min_value' => $this->min_value, 'max_value' => $this->max_value];
        $this->min_value = null;
        $this->max_value = null;

        return $values;
    }
}
