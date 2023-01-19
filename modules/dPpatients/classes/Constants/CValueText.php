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
class CValueText extends CAbstractConstant
{
    /** @var string */
    public const RESOURCE_TYPE = "value_text";

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
        $spec->table = "value_text";
        $spec->key   = "value_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props               = parent::getProps();
        $props["patient_id"] .= " back|constants_text";
        $props["releve_id"]  .= " back|constants_value_text";
        $props["value"]      = "text notNull fieldset|values";

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function storeValues(array $values): CAbstractConstant
    {
        if (($value = CMbArray::get($values, "value")) === null) {
            throw new CConstantException(CConstantException::VALUE_NOT_FOUND);
        }
        $this->value = $value;
        if ($msg = $this->store()) {
            $this->treatErrorStore($msg);
        }

        return $this;
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
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function matchingValues(array $values): bool
    {
        $value = CMbArray::get($values, "value");

        return $value === $this->value;
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
