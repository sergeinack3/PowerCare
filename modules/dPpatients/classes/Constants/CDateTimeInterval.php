<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Constants;

use Ox\Core\CMbDT;

/**
 * Description
 */
class CDateTimeInterval extends CInterval
{
    public const RESOURCE_TYPE = "datetime_interval";
    /** @var string */
    public const FIELDSET_VALUES = 'values';

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "datetime_interval";
        $spec->key   = "value_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props               = parent::getProps();
        $props["patient_id"] .= " back|constants_datetime_interval";
        $props["releve_id"]  .= " back|constants_interval_datetime";
        $props["min_value"]  = "dateTime notNull fieldset|values";
        $props["max_value"]  = "dateTime notNull fieldset|values";

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();
        $this->_input_field = "hidden";
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
        $min      = $this->_ref_spec->min_value;
        $max      = $this->_ref_spec->max_value;
        $duration = CMbDT::minutesRelative($this->min_value, $this->max_value) * 60; //en secondes
        if ($min !== null) {
            if ($duration < $min) {
                return "||" . CConstantException::INVALID_VALUE_UNDER_MINIMUM;
            }
        }

        if ($max !== null) {
            if ($duration > $max) {
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
        $data_alert = [];
        for ($i = 1; $i <= CConstantAlert::$NB_ALERTS; $i++) {
            $seuil_bas  = "seuil_bas_$i";
            $seuil_haut = "seuil_haut_$i";
            $duration   = CMbDT::minutesRelative($this->min_value, $this->max_value) * 60; // en secondes
            if ($duration > $alert->{$seuil_haut} && $alert->{$seuil_haut} !== null) {
                $data_alert ["seuil"] = "haut";
                $data_alert ["level"] = "$i";
            } elseif ($duration < $alert->{$seuil_bas} && $alert->{$seuil_bas} !== null) {
                $data_alert ["seuil"] = "bas";
                $data_alert ["level"] = "$i";
            }
        }

        return $data_alert;
    }
}
