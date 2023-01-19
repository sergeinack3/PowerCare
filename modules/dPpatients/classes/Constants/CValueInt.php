<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Constants;

use Ox\Core\CMbArray;
use Ox\Core\CMbMath;

/**
 * Description
 */
class CValueInt extends CAbstractConstant
{
    /** @var string */
    public const RESOURCE_TYPE = "value_int";

    /** @var string */
    public const FIELDSET_VALUES = 'values';

    //db Field
    /** @var int */
    public $value;

    /**
     * @inheritdoc
     */
    public static function calculateValue(array $constants, string $formula): array
    {
        $vars = [];
        /** @var CAbstractConstant $_constant */
        foreach ($constants as $_constant) {
            if (!$_constant) {
                continue;
            }
            $vars[$_constant->_ref_spec->code] = $_constant->getValue();
        }

        return ["value" => round(CMbMath::evaluate($formula, $vars), 0)];
    }

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "value_int";
        $spec->key   = "value_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props               = parent::getProps();
        $props["patient_id"] .= " back|constants_int";
        $props["releve_id"]  .= " back|constants_value_int";
        $props["value"]      = "num notNull fieldset|values";

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
     * @throws CConstantException
     */
    public function map(array $data): CAbstractConstant
    {
        parent::map($data);

        $value = CMbArray::get($data, 'value');
        if (($unit = CMbArray::get($data, 'unit')) && $value !== null && $this->spec_id) {
            $value = $this->getRefSpec()->convertInPrimaryUnit($unit, $value);
        }
        $this->value = $value;

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
        if (!$this->_ref_spec) {
            return;
        }
        $this->_input_field = "number";
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
        $value = intval(CMbArray::get($values, "value"));

        return $value == intval($this->value);
    }

    /**
     * @inheritdoc
     */
    protected function updateValue(): void
    {
        $this->_view_value = $this->value;
        parent::updateValue();
    }

    /**
     * @inheritdoc
     */
    protected function findAlert(CConstantAlert $alert): array
    {
        $data_alert = [];
        for ($i = 1; $i <= CConstantAlert::$NB_ALERTS; $i++) {
            $seuil_bas  = "seuil_bas_$i";
            $seuil_haut = "seuil_haut_$i";

            if ($this->value > $alert->{$seuil_haut} && $alert->{$seuil_haut} !== null) {
                $data_alert ["seuil"] = "haut";
                $data_alert ["level"] = "$i";
            } elseif ($this->value < $alert->{$seuil_bas} && $alert->{$seuil_bas} !== null) {
                $data_alert ["seuil"] = "bas";
                $data_alert ["level"] = "$i";
            }
        }

        return $data_alert;
    }
}
