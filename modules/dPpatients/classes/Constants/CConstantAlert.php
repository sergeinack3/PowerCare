<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Constants;

use Ox\Core\CAppUI;
use Ox\Core\CStoredObject;

/**
 * Description
 */
class CConstantAlert extends CStoredObject
{

    /** @var int */
    public static $NB_ALERTS = 3;

    /** @var int Primary key */
    public $constant_alert_id;

    //db field
    /** @var int */
    public $spec_id;
    /** @var string */
    public $seuil_bas_1;
    /** @var string */
    public $seuil_bas_2;
    /** @var string */
    public $seuil_bas_3;
    /** @var string */
    public $seuil_haut_1;
    /** @var string */
    public $seuil_haut_2;
    /** @var string */
    public $seuil_haut_3;
    /** @var string */
    public $comment_bas_1;
    /** @var string */
    public $comment_bas_2;
    /** @var string */
    public $comment_bas_3;
    /** @var string */
    public $comment_haut_1;
    /** @var string */
    public $comment_haut_2;
    /** @var string */
    public $comment_haut_3;

    // form field
    /** @var int */
    public $_nb_level_alerts = 0;

    // refs
    /** @var CConstantSpec */
    public $_ref_spec;

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "constant_alert";
        $spec->key   = "constant_alert_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props                     = parent::getProps();
        $props["spec_id"]          = "num notNull"; // ref constantSpec_id peut pas check car constantes par défaut
        $props["seuil_bas_1"]      = "str";
        $props["seuil_bas_2"]      = "str";
        $props["seuil_bas_3"]      = "str";
        $props["seuil_haut_1"]     = "str";
        $props["seuil_haut_2"]     = "str";
        $props["seuil_haut_3"]     = "str";
        $props["comment_haut_1"]   = "text";
        $props["comment_haut_2"]   = "text";
        $props["comment_haut_3"]   = "text";
        $props["comment_bas_1"]    = "text";
        $props["comment_bas_2"]    = "text";
        $props["comment_bas_3"]    = "text";
        $props["_nb_level_alerts"] = "num";

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();
        $this->countLevelAlert();
    }

    /**
     * Count level alert
     *
     * @return int number of alerts
     */
    public function countLevelAlert(): int
    {
        $nb_alert = 0;
        for ($i = 1; $i <= self::$NB_ALERTS; $i++) {
            $seuil_bas  = $this->{"seuil_bas_$i"};
            $seuil_haut = $this->{"seuil_haut_$i"};
            if ($seuil_bas !== null || $seuil_haut !== null) {
                $nb_alert++;
            }
        }

        return $this->_nb_level_alerts = $nb_alert;
    }

    /**
     * To know if level alert is set
     *
     * @param int $level level alert
     *
     * @return bool
     */
    public function hasLevelAlert(int $level): bool
    {
        return $this->{"seuil_haut_$level"} != null || $this->{"seuil_bas_$level"} !== null;
    }

    /**
     * @inheritdoc
     */
    public function check()
    {
        // seuil_bas_* >= seuil_haut_* || seuil_haut_* <= seuil_bas_*
        // seuil_bas_i-n <= seuil_bas_i && seuil_haut_i-n >= seuil_haut_i
        // seuil_*_* && comment_*_*
        for ($i = 1; $i <= self::$NB_ALERTS; $i++) {
            if ($msg = $this->checkMinAndMax($i)) {
                return $msg;
            }

            // seuil_*_* && comment_*_*
            if ($msg = $this->checkDuoSeuilComment($i)) {
                return $msg;
            }

            //seuil_haut_* > seuil_bas_* && seuil_bas_* < seuil_haut_*
            if ($msg = $this->checkLinear($i)) {
                return $msg;
            }

            if ($i == 1) {
                continue;
            }
            // seuil_bas_i-n <= seuil_bas_i && seuil_haut_i-n >= seuil_haut_i
            if ($msg = $this->checkBoundsConsistent($i)) {
                return $msg;
            }
        }

        return parent::check();
    }

    /**
     * Check if seuil_bas is smaller than min_value and seuil_haut is greater than max_value
     *
     * @param int $level_alert level alert
     *
     * @return string
     */
    private function checkMinAndMax(int $level_alert): string
    {
        if (!$this->_ref_spec) {
            $this->_ref_spec = CConstantSpec::getSpecById($this->spec_id);
        }
        $seuil_bas  = $this->{"seuil_bas_$level_alert"};
        $seuil_haut = $this->{"seuil_haut_$level_alert"};
        $min        = $this->_ref_spec->min_value;
        $max        = $this->_ref_spec->max_value;

        if ($min && $seuil_bas < $min) {
            return CAppUI::tr(
                "CConstantAlert-add-msg failed, bound min %s is smaller than min value",
                [CAppUI::tr("CConstantAlert.level.$level_alert")]
            );
        }

        if ($max && $seuil_haut > $max) {
            return CAppUI::tr(
                "CConstantAlert-add-msg failed, bound max %s is greater than max value",
                [CAppUI::tr("CConstantAlert.level.$level_alert")]
            );
        }

        return "";
    }

    /**
     * Check if comment_*_* && seuil_*_*
     *
     * @param int $level_alert Level to check
     *
     * @return string
     */
    private function checkDuoSeuilComment(int $level_alert): string
    {
        $seuil_bas    = $this->{"seuil_bas_$level_alert"};
        $seuil_haut   = $this->{"seuil_haut_$level_alert"};
        $comment_bas  = $this->{"comment_bas_$level_alert"};
        $comment_haut = $this->{"comment_haut_$level_alert"};

        if (!(($seuil_bas === "" && $comment_bas === "") || ($seuil_bas !== "" && $comment_bas !== ""))) {
            return CAppUI::tr(
                "CConstantAlert-add-msg failed, bound or comment min %s is not defined",
                [CAppUI::tr("CConstantAlert.level.$level_alert")]
            );
        }

        if (!(($seuil_haut === "" && $comment_haut === "") || ($seuil_haut !== "" && $comment_haut !== ""))) {
            return CAppUI::tr(
                "CConstantAlert-add-msg failed, bound or comment max %s is not defined",
                [CAppUI::tr("CConstantAlert.level.$level_alert")]
            );
        }

        return "";
    }

    /**
     * Check if seuil_haut_* > seuil_bas_*
     *
     * @param int $level_alert level alert
     *
     * @return string
     */
    private function checkLinear(int $level_alert): string
    {
        $seuil_bas  = $this->{"seuil_bas_$level_alert"};
        $seuil_haut = $this->{"seuil_haut_$level_alert"};

        if (!($seuil_haut === "" || $seuil_haut > $seuil_bas)) {
            return CAppUI::tr(
                "CConstantAlert-add-msg failed, bound max %s is not greater than bound min",
                [CAppUI::tr("CConstantAlert.level.$level_alert")]
            );
        }

        return "";
    }

    /**
     * Check la cochérence des données,
     *
     * @param int $level_alert Level to check
     *
     * @return string error or ""
     */
    private function checkBoundsConsistent(int $level_alert): string
    {
        $level_n    = $level_alert - 1;
        $seuil_bas  = $this->{"seuil_bas_$level_alert"};
        $seuil_haut = $this->{"seuil_haut_$level_alert"};

        for ($i = $level_n; $i >= 1; $i--) {
            $seuil_bas_n  = $this->{"seuil_bas_$i"};
            $seuil_haut_n = $this->{"seuil_haut_$i"};

            if ($seuil_bas > $seuil_bas_n && $seuil_bas_n !== "" && $seuil_bas !== "") {
                return CAppUI::tr(
                    "CConstantAlert-msg-add failed, alert bound min level %s is greater than alert bound min level %s",
                    [CAppUI::tr("CConstantAlert.level.$level_alert"), CAppUI::tr("CConstantAlert.level.$i")]
                );
            }

            if ($seuil_haut < $seuil_haut_n && $seuil_haut_n !== "" && $seuil_haut !== "") {
                return CAppUI::tr(
                    "CConstantAlert-msg-add failed, alert bound max level %s is smaller than alert bound max level %s",
                    [CAppUI::tr("CConstantAlert.level.$level_alert"), CAppUI::tr("CConstantAlert.level.$i")]
                );
            }
        }

        return "";
    }
}
