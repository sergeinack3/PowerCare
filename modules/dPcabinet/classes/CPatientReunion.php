<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Exception;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\Handlers\Events\ObjectHandlerEvent;
use Ox\Mediboard\Patients\CPatient;

/**
 * Classe de liaison entre une réunion et un patient
 */
class CPatientReunion extends CMbObject
{
    /** @var int */
    public $patient_reunion_id;
    /** @var int */
    public $reunion_id;
    /** @var int */
    public $patient_id;
    /** @var string */
    public $motif;
    /** @var string */
    public $remarques;
    /** @var string */
    public $action;
    /** @var string */
    public $au_total;
    /** @var int */
    public $model_id;

    /** @var CReunion */
    public $_ref_reunion;
    /** @var CPatient */
    public $_ref_patient;
    /** @var CConsultation[] */
    public $_refs_consultation = [];
    /** @var CConsultation */
    public $_ref_last_consultation;

    // Form fields
    /** @var int */
    public $_consultation_id;

    public function getSpec(): CMbObjectSpec
    {
        $spec                                = parent::getSpec();
        $spec->table                         = "patient_reunion";
        $spec->key                           = "patient_reunion_id";
        $spec->uniques["patient_reunion_id"] = ["reunion_id", "patient_id"];

        return $spec;
    }

    public function getProps(): array
    {
        $props               = parent::getProps();
        $props["reunion_id"] = "ref class|CReunion notNull back|patients_reunions cascade";
        $props["patient_id"] = "ref class|CPatient notNull back|patient_reunion";
        $props["motif"]      = "text helped";
        $props["remarques"]  = "text helped";
        $props["action"]     = "text helped";
        $props["au_total"]   = "text helped";
        $props["model_id"]   = "num";

        return $props;
    }

    public function store(): ?string
    {
        foreach ($this->loadRefsAppointment() as $_consult) {
            $_consult->next_meeting = 0;
            $_consult->store();
        }

        return parent::store();
    }

    /**
     * @return CConsultation[]
     * @throws Exception
     */
    public function loadRefsAppointment(): array
    {
        $consultations = $this->loadRefPatient()->loadRefsConsultations(["next_meeting" => "= '1'"]);

        return $this->_refs_consultation = $consultations;
    }

    /**
     * @throws Exception
     */
    public function loadRefPatient(): CPatient
    {
        return $this->_ref_patient = $this->loadFwdRef("patient_id", true);
    }

    /**
     * @inheritdoc
     */
    public function delete(): ?string
    {
        if ($this->loadRefLastConsultation()) {
            $this->_ref_last_consultation->next_meeting = 1;
            $this->_ref_last_consultation->store();
        }

        return parent::delete();
    }

    /**
     * @throws Exception
     */
    public function loadRefLastConsultation(): ?CConsultation
    {
        $consultations = (new CConsultation())->loadList(
            ["consultation.patient_id" => "= '" . $this->patient_id . "'"],
            "plageconsult.date desc, consultation.heure desc",
            "0,1",
            null,
            "plageconsult on consultation.plageconsult_id = plageconsult.plageconsult_id"
        );

        $consultation = reset($consultations);

        return $this->_ref_last_consultation = ($consultation) ?: null;
    }

    /**
     * @inheritDoc
     * @throws Exception
     * @see parent::fillTemplate(), used to be detected as context for the documents models
     */
    public function fillTemplate(&$template): void
    {
        $this->notify(ObjectHandlerEvent::BEFORE_FILL_LIMITED_TEMPLATE(), $template);

        $this->loadRefPatient()->fillLimitedTemplate($template);
        $this->loadRefReunion()->fillTemplate($template);

        $template->addProperty('Réunion patient - Motif', $this->motif);
        $template->addProperty('Réunion patient - Remarques', $this->remarques);
        $template->addProperty('Réunion patient - Action', $this->action);
        $template->addProperty('Réunion patient - Au total', $this->au_total);

        parent::fillTemplate($template);
    }

    /**
     * @throws Exception
     */
    public function loadRefReunion(): CReunion
    {
        return $this->_ref_reunion = $this->loadFwdRef("reunion_id");
    }
}
