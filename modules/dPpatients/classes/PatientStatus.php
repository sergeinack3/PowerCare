<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Exception;
use Ox\Core\CMbArray;

/**
 * Gestion du statut du patient en fonction de ses attributs et de la source d'identité sélectionnée
 */
class PatientStatus
{
    /** @var CPatient */
    private $patient;

    /** @var bool */
    private static $updating_status;

    /** @var array */
    private $sources_identite = [];

    /** @var CSourceIdentite */
    private $source_identite;

    public function __construct(CPatient $patient)
    {
        $this->patient = $patient;
    }

    public function updateStatus(): ?string
    {
        if (self::$updating_status) {
            return null;
        }

        self::$updating_status = true;

        $this->setSourcesIdentite(
            $this->patient->_ref_sources_identite ?: $this->patient->loadRefsSourcesIdentite()
        );
        $this->setSourceIdentite(
            $this->patient->_ref_source_identite ?: $this->patient->loadRefSourceIdentite()
        );

        $this->patient->loadRefPatientState();
        $this->patient->completeField('status');

        $status = $this->getStatus();

        $msg = null;

        if ($status && ($this->patient->status !== $status)) {
            $this->patient->status = $status;
            $msg                   = $this->patient->store();
        }

        self::$updating_status = false;

        return $msg;
    }

    public function setSourceIdentite(CSourceIdentite $source_identite): void
    {
        $this->source_identite = $source_identite;
    }

    public function setSourcesIdentite(array $sources_identite): void
    {
        $this->sources_identite = $sources_identite;
    }

    private function getStatus(): ?string
    {
        $status = null;

        // Gestion du statut VIDE lors de la réception de patients
        if (
            $this->patient->status === 'VIDE'
            && (count($this->sources_identite) === 1)
            && ($this->source_identite->mode_obtention === CSourceIdentite::MODE_OBTENTION_INTEROP)
        ) {
            return $status;
        }

        $high_level_identity_proof =
            $this->source_identite->loadRefIdentityProofType()->trust_level === CIdentityProofType::TRUST_LEVEL_HIGH;
        $mode_obtention            = $this->source_identite->getModeObtention();

        $has_justificatif = false;

        foreach ($this->sources_identite as $_source_identite) {
            if (!$_source_identite->active || ($_source_identite->_id === $this->source_identite->_id)) {
                continue;
            }

            if ($_source_identite->identity_proof_type_id) {
                $has_justificatif = true;
            }
        }

        if (
            (!$high_level_identity_proof && $mode_obtention !== CSourceIdentite::MODE_OBTENTION_INSI)
            || ($this->patient->_douteux)
            || ($this->patient->_fictif)
        ) {
            // Statut IV - ; INSi -
            // Pas de type de justificatif à haut niveau de confiance et pas de modification
            // de l'identité sur la base des retours INSi
            // OU le patient a l'attribut "identité douteuse"
            // OU le patient a l'attribut "identité fictive"
            $status = 'PROV';
        } elseif (!$has_justificatif && $mode_obtention === CSourceIdentite::MODE_OBTENTION_INSI) {
            // Statut IV - ; INSi +
            // Pas de type de justificatif à haut niveau de confiance et identité créée sur la base des retours INSi
            $status = 'RECUP';
        } elseif ($high_level_identity_proof && $mode_obtention !== CSourceIdentite::MODE_OBTENTION_INSI) {
            // Statut IV + ; INSi -
            $status = 'VALI';
        } elseif ($has_justificatif && $mode_obtention === CSourceIdentite::MODE_OBTENTION_INSI) {
            // Statut IV + ; INSi +
            $status = 'QUAL';
        }

        return $status;
    }

    /**
     * Désactive la source d'identite insi et active la source par carte vitale ou manuelle
     *
     * @param array $traits_stricts_modified Eventuels traits stricts à reprendre du patient
     * @param int   $service Service utilisé pour demote l'identité
     *
     * @throws Exception
     */
    public function demoteIdentitySource(array $traits_stricts_modified = [], int $service = 5): ?string
    {
        // On désactive la source insi
        $source = $this->source_identite;
        $source->active = 0;

        foreach ($this->sources_identite as $key => $_source) {
            if ($_source->_id === $source->_id || !$_source->active) {
                unset($this->sources_identite[$key]);
            }
        }

        // On reorder pour avoir les justificatifs en premier (ou en dernier si modification de traits stricts)
        CMbArray::pluckSort(
            $this->sources_identite,
            (count($traits_stricts_modified) || $service === 3) ? SORT_ASC : SORT_DESC,
            "identity_proof_type_id"
        );

        // On active la source manuelle
        $this->source_identite               = reset($this->sources_identite);
        $this->patient->_ref_source_identite = $this->source_identite;
        $this->setSourceIdentite($this->source_identite);
        $this->patient->source_identite_id = $this->source_identite->_id;

        // On enregistre après le changement de source sur le patient
        // Impossible de désactiver la source active du patient
        CSourceIdentite::$update_patient_status = false;
        $source->store();
        CSourceIdentite::$update_patient_status = true;

        // On met à jour le statut du patient
        // Dans le cas d'un patient n'ayant pas de source manuelle, il faut que la source
        // créée soit de type manuel (le mode d'obtention est insi car c'était la source active du patient
        $this->patient->_mode_obtention = CSourceIdentite::MODE_OBTENTION_MANUEL;
        $this->updateStatus();

        return $this->patient->status;
    }

    /**
     * Rétrograde le statut de l'identité du patient
     *
     * @param array $traits_stricts_modified Eventuels traits stricts à reprendre du patient
     *
     * @throws Exception
     */
    public static function demotePatientStatus(CPatient $patient, array $traits_stricts_modified = [], int $service = 5): void
    {
        $patient_status = new PatientStatus($patient);

        $patient_status->setSourceIdentite($patient->_ref_source_identite);
        $patient_status->setSourcesIdentite($patient->_ref_sources_identite);

        $patient_status->demoteIdentitySource($traits_stricts_modified, $service);
    }
}
