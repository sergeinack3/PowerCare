<?php

/**
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\BloodSalvage;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CStoredObject;
use Ox\Core\Handlers\Events\ObjectHandlerEvent;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;

/**
 * The blood salvage Class.
 * This class registers informations about an intraoperative blood salvage operation.
 * A blood salvage operation is referenced to an operation (@param $_ref_operation_id )
 */
class CBloodSalvage extends CMbObject
{
    public const SAVED_VOLUME_UNIT      = 'ml';
    public const TRANSFUSED_VOLUME_UNIT = 'ml';
    public const WASH_VOLUME_UNIT       = 'ml';
    public const HGB_POCKET_UNIT        = 'g/dl';
    public const HGB_PATIENT_UNIT       = 'g/dl';

    //DB Table Keyin
    /** @var int */
    public $blood_salvage_id;

    //DB References
    /** @var int */
    public $operation_id;
    /** @var int */
    public $cell_saver_id; // The Cell Saver equipment
    /** @var int */
    public $type_ei_id;        // Reference to an incident type

    //DB Fields
    /** @var int */
    public $wash_volume;       // *Volume de lavage*
    /** @var int */
    public $saved_volume;      // *Volume récupéré pendant la manipulation*
    /** @var int */
    public $hgb_pocket;        // *Hémoglobine de la poche récupérée*
    /** @var int */
    public $hgb_patient;       // *Hémoglobine du patient post transfusion*
    /** @var int */
    public $transfused_volume;
    /** @var int */
    public $anticoagulant_cip; // *Code CIP de l'anticoagulant utilisé*

    /** @var string */
    public $receive_kit_ref;
    /** @var string */
    public $receive_kit_lot;

    /** @var string */
    public $wash_kit_ref;
    /** @var string */
    public $wash_kit_lot;

    /** @var string */
    public $sample;

    // Form Fields
    /** @var string */
    public $_totaltime;
    /** @var string */
    public $_recuperation_start;
    /** @var string */
    public $_recuperation_end;
    /** @var string */
    public $_transfusion_start;
    /** @var string */
    public $_transfusion_end;

    //Distants Fields
    /** @var string */
    public $_datetime;

    //Timers for the operation
    /** @var string */
    public $recuperation_start;
    /** @var string */
    public $recuperation_end;
    /** @var string */
    public $transfusion_start;
    /** @var string */
    public $transfusion_end;

    /** @var COperation */
    public $_ref_operation;

    /** @var CCellSaver */
    public $_ref_cell_saver;

    /** @var CTypeEi */
    public $_ref_incident_type;

    /** @var CPatient */
    public $_ref_patient;

    /**
     * @see parent::getSpec()
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = 'blood_salvage';
        $spec->key   = 'blood_salvage_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    public function getProps(): array
    {
        $props                  = parent::getProps();
        $props["operation_id"]  = "ref notNull class|COperation back|blood_salvages";
        $props["cell_saver_id"] = "ref class|CCellSaver back|blood_salvages";
        $props["type_ei_id"]    = "ref class|CTypeEi back|blood_salvages";

        $props["recuperation_start"] = "dateTime";
        $props["recuperation_end"]   = "dateTime";
        $props["transfusion_start"]  = "dateTime";
        $props["transfusion_end"]    = "dateTime";

        $props["_recuperation_start"] = "time";
        $props["_recuperation_end"]   = "time";
        $props["_transfusion_start"]  = "time";
        $props["_transfusion_end"]    = "time";

        $props["wash_volume"]       = "num";
        $props["saved_volume"]      = "num";
        $props["transfused_volume"] = "num";
        $props["hgb_pocket"]        = "num";
        $props["hgb_patient"]       = "num";
        $props["anticoagulant_cip"] = "numchar length|7";
        $props["wash_kit_ref"]      = "str maxLength|32 autocomplete";
        $props["wash_kit_lot"]      = "str maxLength|32";
        $props["receive_kit_ref"]   = "str maxLength|32 autocomplete";
        $props["receive_kit_lot"]   = "str maxLength|32";
        $props["sample"]            = "enum notNull list|non|prel|trans default|non";

        $props["_datetime"] = "dateTime";

        return $props;
    }

    /**
     * @throws Exception
     * @see parent::loadRefsFwd()
     */
    public function loadRefsFwd(): void
    {
        parent::loadRefsFwd();
        $this->loadRefOperation();
        $this->loadRefPatient();
        $this->loadRefCellSaver();
        $this->loadRefTypeEi();
        $this->_view = "RSPO de {$this->_ref_patient->_view}";
    }

    /**
     * Chargement du patient
     *
     * @return CPatient
     */
    public function loadRefPatient(): CPatient
    {
        return $this->_ref_patient = $this->_ref_operation->loadRefPatient();
    }

    /**
     * Chargement de l'opération
     *
     * @return COperation
     * @throws Exception
     */
    public function loadRefOperation(): COperation
    {
        /** @var COperation $operation */
        $operation = $this->loadFwdRef("operation_id", true);
        $operation->loadRefPlageOp();

        return $this->_ref_operation = $operation;
    }

    /**
     * Chargement de Cell Saver
     *
     * @return CStoredObject
     * @throws Exception
     */
    public function loadRefCellSaver(): CStoredObject
    {
        return $this->_ref_cell_saver = $this->loadFwdRef("cell_saver_id", true);
    }

    /**
     * Chargement de l'incident
     *
     * @return CStoredObject
     * @throws Exception
     */
    public function loadRefTypeEi(): CStoredObject
    {
        return $this->_ref_incident_type = $this->loadFwdRef("type_ei_id", true);
    }

    /**
     * Chargement de la plage opératoire
     *
     * @return void
     * @throws Exception
     */
    public function loadRefPlageOp(): void
    {
        $operation       = $this->loadRefOperation();
        $this->_datetime = $operation->_datetime;
    }

    /**
     * @see parent::updateFormFields()
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();
        if ($this->recuperation_start) {
            $this->_recuperation_start = CMbDT::time($this->recuperation_start);
        }
        if ($this->recuperation_end) {
            $this->_recuperation_end = CMbDT::time($this->recuperation_end);
        }
        if ($this->transfusion_start) {
            $this->_transfusion_start = CMbDT::time($this->transfusion_start);
        }
        if ($this->transfusion_end) {
            $this->_transfusion_end = CMbDT::time($this->transfusion_end);
        }
    }

    /**
     * @throws Exception
     * @see parent::updatePlainFields()
     */
    public function updatePlainFields(): void
    {
        parent::updatePlainFields();
        $this->loadRefPlageOp();

        if ($this->_recuperation_start == "current") {
            $this->_recuperation_start = CMbDT::time();
        }
        if ($this->_recuperation_end == "current") {
            $this->_recuperation_end = CMbDT::time();
        }
        if ($this->_transfusion_start == "current") {
            $this->_transfusion_start = CMbDT::time();
        }
        if ($this->_transfusion_end == "current") {
            $this->_transfusion_end = CMbDT::time();
        }

        if ($this->_recuperation_start !== null && $this->_recuperation_start != "") {
            $this->_recuperation_start = CMbDT::time($this->_recuperation_start);
            $this->recuperation_start  = CMbDT::addDateTime($this->_recuperation_start, CMbDT::date($this->_datetime));
        }
        if ($this->_recuperation_start === "") {
            $this->recuperation_start = "";
        }
        if ($this->_recuperation_end !== null && $this->_recuperation_end != "") {
            $this->_recuperation_end = CMbDT::time($this->_recuperation_end);
            $this->recuperation_end  = CMbDT::addDateTime($this->_recuperation_end, CMbDT::date($this->_datetime));
        }
        if ($this->_recuperation_end === "") {
            $this->recuperation_end = "";
        }
        if ($this->_transfusion_start !== null && $this->_transfusion_start != "") {
            $this->_transfusion_start = CMbDT::time($this->_transfusion_start);
            $this->transfusion_start  = CMbDT::addDateTime($this->_transfusion_start, CMbDT::date($this->_datetime));
        }
        if ($this->_transfusion_start === "") {
            $this->transfusion_start = "";
        }
        if ($this->_transfusion_end !== null && $this->_transfusion_end != "") {
            $this->_transfusion_end = CMbDT::time($this->_transfusion_end);
            $this->transfusion_end  = CMbDT::addDateTime($this->_transfusion_end, CMbDT::date($this->_datetime));
        }
        if ($this->_transfusion_end === "") {
            $this->transfusion_end = "";
        }
    }

    /**
     * @throws Exception
     * @see parent::fillTemplate()
     */
    public function fillTemplate(&$template): void
    {
        parent::fillTemplate($template);
        $this->fillLimitedTemplate($template);
    }

    /**
     * @param CTemplateManager $template
     *
     * @throws Exception
     */
    public function fillLimitedTemplate(&$template): void
    {
        parent::fillLimitedTemplate($template);
        $this->loadRefCellSaver();
        $this->loadRefTypeEi();

        $this->notify(ObjectHandlerEvent::BEFORE_FILL_LIMITED_TEMPLATE(), $template);

        $cell_saver_tr = CAppUI::tr('CCellSaver') . ' - ';

        $template->addProperty(
            $cell_saver_tr . CAppUI::tr('CCellSaver-modele-desc_used'),
            $this->_ref_cell_saver->_view
        );
        $template->addDateTimeProperty(
            $cell_saver_tr . CAppUI::tr("CBloodSalvage-_recuperation_start"),
            $this->recuperation_start
        );
        $template->addDateTimeProperty(
            $cell_saver_tr . CAppUI::tr("CBloodSalvage-_recuperation_end"),
            $this->recuperation_end
        );
        $template->addDateTimeProperty(
            $cell_saver_tr . CAppUI::tr("CBloodSalvage-_transfusion_start"),
            $this->transfusion_start
        );
        $template->addDateTimeProperty(
            $cell_saver_tr . CAppUI::tr("CBloodSalvage-_transfusion_fin"),
            $this->transfusion_end
        );
        $template->addProperty(
            $cell_saver_tr . CAppUI::tr("CBloodSalvage-Volume_retrieved"),
            $this->saved_volume . " " . self::SAVED_VOLUME_UNIT
        );
        $template->addProperty(
            $cell_saver_tr . CAppUI::tr("CBloodSalvage-wash_volume"),
            $this->wash_volume . " " . self::WASH_VOLUME_UNIT
        );
        $template->addProperty(
            $cell_saver_tr . CAppUI::tr("CBloodSalvage-transfused_volume"),
            $this->transfused_volume . " " . self::TRANSFUSED_VOLUME_UNIT
        );
        $template->addProperty(
            $cell_saver_tr . CAppUI::tr("CBloodSalvage-hgb_pocket-desc"),
            $this->hgb_pocket . " " . self::HGB_POCKET_UNIT
        );
        $template->addProperty(
            $cell_saver_tr . CAppUI::tr("CBloodSalvage-hgb_patient-desc"),
            $this->hgb_patient . " " . self::HGB_PATIENT_UNIT
        );

        if ($this->_ref_incident_type->_view) {
            $template->addProperty(
                $cell_saver_tr . CAppUI::tr("CBloodSalvage-type_ei_id-court"),
                $this->_ref_incident_type->_view
            );
        } else {
            $template->addProperty(
                $cell_saver_tr . CAppUI::tr("CBloodSalvage-type_ei_id-court"),
                CAppUI::tr("CTypeEi.type_signalement.none_signaled")
            );
        }

        $this->notify(ObjectHandlerEvent::AFTER_FILL_LIMITED_TEMPLATE(), $template);
    }
}
