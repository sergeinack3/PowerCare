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
use Ox\Core\CStoredObject;
use Ox\Core\Handlers\Events\ObjectHandlerEvent;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Meetings
 */
class CReunion extends CMbObject
{
    /** @var int */
    public $reunion_id;
    /** @var string */
    public $motif;
    /** @var string */
    public $remarques;
    /** @var int */
    public $rappel;

    /** @var CPatientReunion[] */
    public $_refs_patient_reunion = [];
    /** @var CMediusers[] */
    public $_refs_practitioners;
    /** @var CConsultation[] */
    public $_refs_consult;


    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = "reunion";
        $spec->key   = "reunion_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props              = parent::getProps();
        $props["motif"]     = "text helped";
        $props["remarques"] = "text helped";
        $props["rappel"]    = "bool default|0";

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function getTemplateClasses(): array
    {
        $tab = parent::getTemplateClasses();

        $tab["CConsultation"] = true;

        return $tab;
    }

    /**
     * @see parent::fillTemplate(), used to be detected as context for the documents models
     * @inheritDoc
     */
    public function fillTemplate(&$template): void
    {
        $this->notify(ObjectHandlerEvent::BEFORE_FILL_LIMITED_TEMPLATE(), $template);

        $appointments = $this->loadRefsAppointment();
        $slots        = CStoredObject::massLoadFwdRef($appointments, "plageconsult_id");
        CStoredObject::massLoadFwdRef($slots, "chir_id");

        $practitioners = array_map(
            function (CConsultation $appointment) {
                return $appointment->loadRefPlageConsult()->loadRefChir();
            },
            $appointments
        );

        $template->addProperty('Réunion - Motif', $this->motif);
        $template->addProperty('Réunion - Remarques', $this->remarques);
        $template->addListProperty("Réunion - Praticiens présents", $practitioners);

        parent::fillTemplate($template);
    }

    /**
     * Loads patients of the meeting
     *
     * @return CPatientReunion[]|null
     * @throws Exception
     */
    public function loadRefsPatientReunion(): ?array
    {
        return $this->_refs_patient_reunion = $this->loadBackRefs("patients_reunions");
    }

    /**
     * When editing some props of the meeting, change props of the linked consultation
     *
     * @param CMbObject|null $class
     *
     * @return string|null
     * @throws Exception
     */
    public function store(?CMbObject $class = null): ?string
    {
        if ($class === null || !$class instanceof CConsultation) {
            // Change the reason in each consultation to have the display on the planning
            $consultations = $this->loadRefsAppointment();
            foreach ($consultations as $_consultation) {
                $_consultation->motif = $this->motif;
                $_consultation->rques = $this->remarques;
                $_consultation->store();
            }
        }

        return parent::store();
    }

    /**
     * Loads the consultations
     *
     * @return CStoredObject[]|null
     * @throws Exception
     */
    public function loadRefsAppointment(): ?array
    {
        return $this->_refs_consult = $this->loadBackRefs("consultation");
    }
}
