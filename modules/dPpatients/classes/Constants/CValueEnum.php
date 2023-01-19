<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Constants;

use Ox\Core\CMbArray;

/**
 * Description
 */
class CValueEnum extends CAbstractConstant
{
    /** @var string  */
    public const RESOURCE_TYPE = "value_enum";
    /** @var string */
    public const FIELDSET_VALUES = 'values';

    //db Field
    /** @var string */
    public $value;

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "value_enum";
        $spec->key   = "value_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props               = parent::getProps();
        $props["patient_id"] .= " back|constants_enum";
        $props["releve_id"]  .= " back|constants_value_enum";
        $props["value"]      = "str notNull fieldset|values";

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function storeValues(array $values): CAbstractConstant
    {
        if (($val = CMbArray::get($values, "value")) === null) {
            throw new CConstantException(CConstantException::VALUE_NOT_FOUND);
        }
        $this->value = $val;
        if ($msg = $this->store()) {
            $this->treatErrorStore($msg);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function check()
    {
        if ($this->_forced_store) {
            return parent::check();
        }

        if (!$this->_ref_spec) {
            $this->getRefSpec();
        }

        $list = explode("|", $this->_ref_spec->list);
        if (!CMbArray::in($this->value, $list)) {
            return "||" . CConstantException::INVALID_VALUE_NOT_AUTHORIZED;
        }
        $min = $this->_ref_spec->min_value;
        $max = $this->_ref_spec->max_value;
        if ($min !== null) {
            if ($this->value < $min) {
                return "||" . CConstantException::INVALID_VALUE_UNDER_MINIMUM;
            }
        }

        if ($max !== null) {
            if ($this->value > $max) {
                return "||" . CConstantException::INVALID_VALUE_UPPER_MAXIMUM;
            }
        }

        return parent::check();
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();
        $this->_input_field = "text";
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return [$this->value];
    }

    /**
     * @inheritdoc
     */
    public function matchingValues(array $values): bool
    {
        $val = CMbArray::get($values, "value");

        return $this->value === $val;
    }

    /**
     * @return array
     */
    public function extractValues(): array
    {
        $values = ['value' => $this->value];
        $this->value = null;

        return $values;
    }

    /**
     * @param array $data
     *
     * @return CAbstractConstant
     */
    public function map(array $data): CAbstractConstant
    {
        $this->value = CMbArray::get($data, 'value');

        return parent::map($data);
    }

    /**
     * @inheritdoc
     */
    protected function updateValue(): void
    {
        $this->_view_value = $this->value;
        parent::updateValue();
    }
}
