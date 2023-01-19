<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Validator;

use Ox\Core\Specification\SpecificationViolation;
use Ox\Import\Framework\Entity\ActeCCAM;
use Ox\Import\Framework\Entity\ActeNGAP;
use Ox\Import\Framework\Entity\Affectation;
use Ox\Import\Framework\Entity\Antecedent;
use Ox\Import\Framework\Entity\Constante;
use Ox\Import\Framework\Entity\Consultation;
use Ox\Import\Framework\Entity\ConsultationAnesth;
use Ox\Import\Framework\Entity\Correspondant;
use Ox\Import\Framework\Entity\DossierMedical;
use Ox\Import\Framework\Entity\EvenementPatient;
use Ox\Import\Framework\Entity\File;
use Ox\Import\Framework\Entity\Injection;
use Ox\Import\Framework\Entity\Medecin;
use Ox\Import\Framework\Entity\Operation;
use Ox\Import\Framework\Entity\Patient;
use Ox\Import\Framework\Entity\PlageConsult;
use Ox\Import\Framework\Entity\Sejour;
use Ox\Import\Framework\Entity\Traitement;
use Ox\Import\Framework\Entity\User;
use Ox\Import\Framework\Entity\Vaccination;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationAbnormalFlag;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationExam;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationFile;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationIdentifier;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationPatient;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationResponsible;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationResultSet;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationResultValue;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationValueUnit;
use Ox\Mediboard\OxLaboServer\Import\Entity\ObservationResult;

/**
 * Description
 */
interface ValidatorVisitorInterface
{
    /**
     * Validate an external user
     *
     * @param User $user
     *
     * @return SpecificationViolation|null
     */
    public function validateUser(User $user): ?SpecificationViolation;

    /**
     * Validate an external patient
     *
     * @param Patient $patient
     *
     * @return SpecificationViolation|null
     */
    public function validatePatient(Patient $patient): ?SpecificationViolation;

    /**
     * Validate an external medecin
     *
     * @param Medecin $medecin
     *
     * @return SpecificationViolation|null
     */
    public function validateMedecin(Medecin $medecin): ?SpecificationViolation;

    /**
     * Validate an external plage consult
     *
     * @param PlageConsult $plage_consult
     *
     * @return SpecificationViolation|null
     */
    public function validatePlageConsult(PlageConsult $plage_consult): ?SpecificationViolation;

    /**
     * Validate an external consultation anesth
     *
     * @param Consultation $consultation
     *
     * @return SpecificationViolation|null
     */
    public function validateConsultation(Consultation $consultation): ?SpecificationViolation;

    /**
     * Validate an external consultation
     *
     * @param ConsultationAnesth $consultation
     *
     * @return SpecificationViolation|null
     */
    public function validateConsultationAnesth(ConsultationAnesth $consultation): ?SpecificationViolation;

    /**
     * Validate an external sejour
     *
     * @param Sejour $sejour
     *
     * @return SpecificationViolation|null
     */
    public function validateSejour(Sejour $sejour): ?SpecificationViolation;

    /**
     * Validate an external file
     *
     * @param File $file
     *
     * @return SpecificationViolation|null
     */
    public function validateFile(File $file): ?SpecificationViolation;

    /**
     * Validate an external affectation
     *
     * @param Affectation $affectation
     *
     * @return SpecificationViolation|null
     */
    public function validateAffectation(Affectation $affectation): ?SpecificationViolation;

    public function validateAntecedent(Antecedent $antecedent): ?SpecificationViolation;

    public function validateTraitement(Traitement $traitement): ?SpecificationViolation;

    public function validateCorrespondant(Correspondant $correspondant): ?SpecificationViolation;

    public function validateEvenementPatient(EvenementPatient $evenement_patient): ?SpecificationViolation;

    public function validateInjection(Injection $injection): ?SpecificationViolation;

    public function validateActeCCAM(ActeCCAM $acte_ccam): ?SpecificationViolation;

    public function validateActeNGAP(ActeNGAP $acte_ngap): ?SpecificationViolation;

    public function validateConstante(Constante $constante): ?SpecificationViolation;

    public function validateDossierMedical(DossierMedical $dossier_medical): ?SpecificationViolation;

    public function validateOperation(Operation $operation): ?SpecificationViolation;

    /**
     * Validate an external observation result
     *
     * @param ObservationResult $observation_result
     *
     * @return SpecificationViolation|null
     */
    public function validateObservationResult(ObservationResult $observation_result): ?SpecificationViolation;

    /**
     * Validate an external observation identifier
     *
     * @param ObservationIdentifier $observation_identifier
     *
     * @return SpecificationViolation|null
     */
    public function validateObservationIdentifier(ObservationIdentifier $observation_identifier
    ): ?SpecificationViolation;

    /**
     * Validate an external observation result value
     *
     * @param ObservationResultValue $observation_result_value
     *
     * @return SpecificationViolation|null
     */
    public function validateObservationResultValue(ObservationResultValue $observation_result_value
    ): ?SpecificationViolation;

    /**
     * Validate an external observation result set
     *
     * @param ObservationResultSet $observation_result_set
     *
     * @return SpecificationViolation|null
     */
    public function validateObservationResultSet(ObservationResultSet $observation_result_set): ?SpecificationViolation;

    /**
     * Validate an external observation normal flag
     *
     * @param ObservationAbnormalFlag $observation_abnormal_flag
     *
     * @return SpecificationViolation|null
     */
    public function validateObservationAbnormalFlag(ObservationAbnormalFlag $observation_flag): ?SpecificationViolation;

    /**
     * Validate an external observation value unit
     *
     * @param ObservationValueUnit $observation_value_unit
     *
     * @return SpecificationViolation|null
     */
    public function validateObservationValueUnit(ObservationValueUnit $observation_value_unit): ?SpecificationViolation;

    /**
     * Validate an external observation file
     *
     * @param ObservationFile $observation_file
     *
     * @return SpecificationViolation|null
     */
    public function validateObservationFile(ObservationFile $observation_file): ?SpecificationViolation;

    /**
     * Validate an external observation responsible
     *
     * @param ObservationResponsible $observation_responsible
     *
     * @return SpecificationViolation|null
     */
    public function validateObservationResponsible(
        ObservationResponsible $observation_responsible
    ): ?SpecificationViolation;

    /**
     * Validate an external observation exam
     *
     * @param ObservationExam $observation_exam
     *
     * @return SpecificationViolation|null
     */
    public function validateObservationExam(
        ObservationExam $observation_exam
    ): ?SpecificationViolation;

    /**
     * Validate an external observation patient
     *
     * @param ObservationExam $observation_exam
     *
     * @return SpecificationViolation|null
     */
    public function validateObservationPatient(
        ObservationPatient $observation_patient
    ): ?SpecificationViolation;
}
