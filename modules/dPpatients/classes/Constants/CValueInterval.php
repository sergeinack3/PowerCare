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
class CValueInterval extends CInterval
{
    /** @var string  */
    public const RESOURCE_TYPE = "value_interval";

    /** @var string */
    public const FIELDSET_VALUES = 'values';

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "value_interval";
        $spec->key   = "value_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props               = parent::getProps();
        $props["patient_id"] .= " back|constants_interval";
        $props["releve_id"]  .= " back|constants_interval_value";
        $props["min_value"]  = "num notNull fieldset|values";
        $props["max_value"]  = "num notNull fieldset|values";

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return parent::getValue();
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
            if ($this->min_value < $min) {
                return "||" . CConstantException::INVALID_VALUE_UNDER_MINIMUM;
            }
        }

        if ($max !== null) {
            if ($this->max_value > $max) {
                return "||" . CConstantException::INVALID_VALUE_UPPER_MAXIMUM;
            }
        }

        return parent::check();
    }

    /**
     * @inheritdoc
     */
    protected function updateValue(): void
    {
        $this->_view_value = $this->min_value . " || " . $this->max_value;
        parent::updateValue();
    }

    /**
     * @inheritdoc
     */
    protected function findAlert(CConstantAlert $alert): array
    {
        //todo a vérifier
        $data_alert = [];
        for ($i = 1; $i <= CConstantAlert::$NB_ALERTS; $i++) {
            $seuil_bas  = "seuil_bas_$i";
            $seuil_haut = "seuil_haut_$i";
            if ($this->min_value > $alert->{$seuil_haut} && $alert->{$seuil_haut} !== null) {
                $data_alert ["seuil"] = "haut";
                $data_alert ["level"] = "$i";
            } elseif ($this->max_value < $alert->{$seuil_bas} && $alert->{$seuil_bas} !== null) {
                $data_alert ["seuil"] = "bas";
                $data_alert ["level"] = "$i";
            }
        }

        return $data_alert;
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

        $min_value = CMbArray::get($data, 'min_value');
        $max_value = CMbArray::get($data, 'max_value');
        if (($unit = CMbArray::get($data, 'unit')) && $min_value !== null && $max_value !== null && $this->spec_id) {
            $min_value = $this->getRefSpec()->convertInPrimaryUnit($unit, $min_value);
            $max_value = $this->getRefSpec()->convertInPrimaryUnit($unit, $max_value);
        }
        $this->min_value = $min_value;
        $this->min_value = $max_value;

        return $this;
    }
}
