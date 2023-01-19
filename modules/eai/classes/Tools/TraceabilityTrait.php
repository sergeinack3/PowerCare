<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Tools;

use Ox\Interop\Eai\CInteropActor;
use Ox\Mediboard\Files\CFileTraceability;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

/**
 * trait CConsultationTrait
 * Consultation utilities EAI
 */
trait TraceabilityTrait
{
    protected function generateTraceabilityHelper(CInteropActor $actor, ?CPatient $patient): CFileTraceability
    {
        $traceability = new CFileTraceability();
        $traceability->user_id           = CMediusers::get()->_id;
        $traceability->actor_class       = $actor->_class;
        $traceability->actor_id          = $actor->_id;
        $traceability->group_id          = $actor->group_id;
        $traceability->source_name       = CFileTraceability::getSourceName($actor);

        // Par défaut le statut est "en attente"
        $traceability->status    = "pending";
        $traceability->initiator = CFileTraceability::INITIATOR_SERVER;

        // Récupération des informations du patient du message
        if ($patient) {
            $traceability->patient_name          = $patient->nom;
            $traceability->patient_birthname     = $patient->nom_jeune_fille;
            $traceability->patient_firstname     = $patient->prenom;
            $traceability->patient_date_of_birth = $patient->naissance;
            $traceability->patient_sexe = $patient->sexe;
        }

        return $traceability;
    }
}
